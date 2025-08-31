<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\SmItemIssueRequest;
use App\Http\Requests\Admin\Inventory\SmItemSellRequest;
use App\Models\StudentRecord;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmClass;
use App\SmInventoryPayment;
use App\SmItem;
use App\SmItemCategory;
use App\SmItemIssue;
use App\SmItemReceive;
use App\SmItemReceiveChild;
use App\SmItemSell;
use App\SmItemSellChild;
use App\SmParent;
use App\SmPaymentMethhod;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use App\SmSupplier;
use App\Traits\NotificationSend;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\RolePermission\Entities\InfixRole;

class SmItemSellController extends Controller
{
    use NotificationSend;



    // This is for upadate database sm_item_sells && sm_item_sell_children table for the issue of  float/double datatype only stores 8 digits.

    public static function updateSmItemSellDatabase(): ?string
    {
        try {
            Schema::table('sm_item_sells', function (Blueprint $blueprint): void {
                $blueprint->decimal('grand_total', 20, 2)->change();
                $blueprint->decimal('total_quantity', 20, 2)->change();
                $blueprint->decimal('total_paid', 20, 2)->change();
                $blueprint->decimal('total_due', 20, 2)->change();
            });
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    public static function updateSmItemSellChildrenDatabase(): ?string
    {
        try {
            Schema::table('sm_item_sell_children', function (Blueprint $blueprint): void {
                $blueprint->decimal('sell_price', 20, 2)->change();
                $blueprint->decimal('quantity', 20, 2)->change();
                $blueprint->decimal('sub_total', 20, 2)->change();
            });
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    public function itemSell(Request $request)
    {
        /*
        try {
        */
            $user = Auth::user();
            $sell_heads = SmChartOfAccount::where('type', 'I')->select(['head', 'id'])->get();

            $account_id = SmBankAccount::where('school_id', $user->school_id)->select(['id', 'account_name', 'bank_name'])->get();

            $suppliers = SmSupplier::select(['id', 'company_name'])->get();
            $items = SmItem::select(['item_name', 'id'])->get();
            $roles = InfixRole::where('is_saas', 0)->when((generalSetting()->with_guardian !== 1), function ($query): void {
                $query->where('id', '!=', 3);
            })->where(function ($q) use ($user): void {
                $q->where('school_id', $user->school_id)->orWhere('type', 'System');
            })->select(['id', 'name', 'type'])
                ->get();

            $classes = SmClass::select(['class_name', 'id'])->get();
            $paymentMethhods = SmPaymentMethhod::select(['method', 'id'])->get();

            return view('backEnd.inventory.itemSell', ['suppliers' => $suppliers, 'items' => $items, 'paymentMethhods' => $paymentMethhods, 'roles' => $roles, 'classes' => $classes, 'sell_heads' => $sell_heads, 'account_id' => $account_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function getReceiveItem()
    {
        /*
        try {
        */
            $searchData = SmItem::where('school_id', Auth::user()->school_id)->select(['id', 'item_name'])->get();
            if (! empty($searchData)) {
                return json_encode($searchData);
            }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }


    public function saveItemSellData(SmItemSellRequest $smItemSellRequest)
    {
        if ($smItemSellRequest->totalPaid > $smItemSellRequest->subTotalValue) {
            Toastr::error('Total Paid can not be greater than Sub Total', 'Failed');

            return redirect()->back()->withInput();
        }
        /*
        try {
        */
            $total_paid = empty($smItemSellRequest->totalPaidValue) ? $smItemSellRequest->totalPaid : $smItemSellRequest->totalPaidValue;
            $subTotalValue = round($smItemSellRequest->subTotalValue);
            $totalDueValue = round($smItemSellRequest->totalDueValue);
            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }

            $student_staff_id = '';
            if (! empty($smItemSellRequest->student)) {
                $student_staff_id = $smItemSellRequest->student;
            }

            if (! empty($smItemSellRequest->staff_id)) {
                $student_staff_id = $smItemSellRequest->staff_id;
            }

            $smItemSell = new SmItemSell();
            $smItemSell->role_id = $smItemSellRequest->role_id;
            $smItemSell->student_staff_id = $student_staff_id;
            $smItemSell->reference_no = $smItemSellRequest->reference_no;
            $smItemSell->sell_date = date('Y-m-d', strtotime($smItemSellRequest->sell_date));
            if (@$smItemSellRequest->subTotalValue) {
                $smItemSell->grand_total = $smItemSellRequest->subTotalValue;
            }

            if (@$smItemSellRequest->subTotalQuantityValue) {
                $smItemSell->total_quantity = $smItemSellRequest->subTotalQuantityValue;
            }

            $smItemSell->total_paid = $total_paid;
            $smItemSell->paid_status = $paid_status;
            $smItemSell->total_due = $smItemSellRequest->totalDueValue;
            $smItemSell->account_id = $smItemSellRequest->bank_id;
            $smItemSell->income_head_id = $smItemSellRequest->income_head_id;
            $smItemSell->payment_method = $smItemSellRequest->payment_method;
            $smItemSell->description = $smItemSellRequest->description;
            $smItemSell->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItemSell->un_academic_id = getAcademicId();
            } else {
                $smItemSell->academic_id = getAcademicId();
            }

            $results = $smItemSell->save();
            $smItemSell->toArray();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Item Sell';
            $smAddIncome->date = date('Y-m-d', strtotime($smItemSellRequest->sell_date));
            $smAddIncome->amount = $total_paid;
            $smAddIncome->item_sell_id = $smItemSell->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $smItemSellRequest->income_head_id;
            $smAddIncome->account_id = $smItemSellRequest->bank_id;
            $smAddIncome->payment_method_id = $smItemSellRequest->payment_method;
            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            } else {
                $smAddIncome->academic_id = getAcademicId();
            }

            $smAddIncome->save();

            if (paymentMethodName($smItemSellRequest->payment_method)) {
                $bank = SmBankAccount::where('id', $smItemSellRequest->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $total_paid;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $total_paid;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = 'Item Sell Payment';
                $smBankStatement->item_sell_id = $smItemSell->id;
                $smBankStatement->payment_date = date('Y-m-d h:i:sa', strtotime($smItemSellRequest->sell_date));
                $smBankStatement->bank_id = $smItemSellRequest->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smItemSellRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smItemSellRequest->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            $itemName = [];

            if ($results) {
                $item_ids = count($smItemSellRequest->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (! empty($smItemSellRequest->item_id[$i])) {
                        $itemSellChild = new SmItemSellChild();
                        $itemSellChild->item_sell_id = $smItemSell->id;
                        $itemSellChild->item_id = $smItemSellRequest->item_id[$i];
                        $itemSellChild->sell_price = $smItemSellRequest->unit_price[$i];
                        $itemSellChild->quantity = $smItemSellRequest->quantity[$i];
                        $itemSellChild->sub_total = $smItemSellRequest->totalValue[$i];
                        $itemSellChild->created_by = Auth()->user()->id;
                        $itemSellChild->school_id = Auth::user()->school_id;
                        if (! moduleStatusCheck('University')) {
                            $itemSellChild->academic_id = getAcademicId();
                        }

                        $result = $itemSellChild->save();
                        $itemName[] = $itemSellChild->items->item_name;

                        if ($result) {
                            $items = SmItem::find($smItemSellRequest->item_id[$i]);
                            $items->total_in_stock -= $smItemSellRequest->quantity[$i];
                            if (moduleStatusCheck('University')) {
                                $items->un_academic_id = getAcademicId();
                            } else {
                                $items->academic_id = getAcademicId();
                            }

                            $results = $items->update();
                        }
                    }
                }
            }

            $data['title'] = 'Item Sell';
            $data['total_paid'] = $total_paid;
            $data['quantity'] = $smItemSellRequest->subTotalQuantityValue;
            $data['item'] = implode(', ', $itemName);
            $this->sent_notifications('Item_sell', [auth()->user()->id], $data, ['1']);

            Toastr::success('Operation successful', 'Success');

            return redirect('item-sell-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function checkProductQuantity()
    {
        /*
        try {
        */
            $product_id = $_POST['product_id'];
            $product_quantity = SmItem::select('total_in_stock')->where('id', $product_id)->first();

            return $product_quantity->total_in_stock;
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function itemSellList()
    {
        /*
        try {
        */
            $allItemSellLists = SmItemSell::where('active_status', '=', 1)
                ->where('school_id', Auth::user()->school_id)
                ->orderby('id', 'DESC')
                ->select(['id','role_id', 'student_staff_id', 'sell_date', 'reference_no', 'grand_total', 'total_due', 'total_paid', 'grand_total', 'total_quantity', 'paid_status'])
                ->with('studentDetails', 'staffDetails', 'parentsDetails')
                ->get();

            return view('backEnd.inventory.itemSellList', ['allItemSellLists' => $allItemSellLists]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewItemSell($id)
    {
        /*
        try {
        */
            $general_setting = generalSetting();
            $viewData = SmItemSell::find($id);
            $editDataChildren = SmItemSellChild::with('items')->where('item_sell_id', $id)->get();

            return view('backEnd.inventory.viewItemSell', ['viewData' => $viewData, 'editDataChildren' => $editDataChildren, 'general_setting' => $general_setting]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewItemSellPrint($id)
    {
/*
        try {
        */
            $viewData = SmItemSell::find($id);
            $editDataChildren = SmItemSellChild::with('items')->where('item_sell_id', $id)->get();
            $pdf = Pdf::loadView('backEnd.inventory.item_sell_print', ['viewData' => $viewData, 'editDataChildren' => $editDataChildren]);
            return $pdf->stream(date('d-m-Y') . '-item-sell-paid.pdf');
            return view('backEnd.inventory.item_sell_print', compact('viewData', 'editDataChildren'));
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function editItemSell(Request $request, $id)
    {
        /*
        try {
        */
            $editData = SmItemSell::find($id);

            $roles = InfixRole::where('is_saas', 0)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            $sell_heads = SmChartOfAccount::where('type', 'I')
                ->get();

            $account_id = SmBankAccount::where('school_id', Auth::user()->school_id)->get();

            $studentClassSection = '';
            $allStudentsSameClassSection = '';
            $staffsByRole = '';
            if ($editData->role_id == 2) {
                $studentClassSection = SmStudent::where('id', $editData->student_staff_id)->first();
                $student_ids = StudentRecord::when($studentClassSection->defaultClass, function ($q) use ($studentClassSection): void {
                    $q->where('class_id', $studentClassSection->defaultClass->class_id)->where('section_id', $studentClassSection->defaultClass->section_id);
                })->pluck('student_id')->unique()->toArray();
                $allStudentsSameClassSection = SmStudent::whereIn('id', $student_ids)->get();
            } elseif ($editData->role_id == 3) {
                $staffsByRole = SmParent::where('active_status', 1)
                    ->get();
            } else {
                $staffsByRole = SmStaff::where('role_id', $editData->role_id)

                    ->get();
            }

            $editDataChildren = SmItemSellChild::where('item_sell_id', $id)

                ->get();

            $items = SmItem::get();
            $classes = SmClass::get();
            $sections = SmSection::get();

            $paymentMethhods = SmPaymentMethhod::get();

            return view('backEnd.inventory.editItemSell', ['editData' => $editData, 'editDataChildren' => $editDataChildren, 'roles' => $roles, 'items' => $items, 'paymentMethhods' => $paymentMethhods, 'classes' => $classes, 'sections' => $sections, 'studentClassSection' => $studentClassSection, 'allStudentsSameClassSection' => $allStudentsSameClassSection, 'staffsByRole' => $staffsByRole, 'sell_heads' => $sell_heads, 'account_id' => $account_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function UpdateItemSellData(SmItemSellRequest $smItemSellRequest)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /*
        try {
        */
            $total_paid = empty($smItemSellRequest->totalPaidValue) ? $smItemSellRequest->totalPaid : $smItemSellRequest->totalPaidValue;
            $subTotalValue = round($smItemSellRequest->subTotalValue);
            $totalDueValue = round($smItemSellRequest->totalDueValue);

            $paid_status = '';

            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }

            $student_staff_id = '';
            if ($smItemSellRequest->role_id == 2) {
                $student_staff_id = $smItemSellRequest->student;
            } elseif ($smItemSellRequest->role_id !== 2) {
                $student_staff_id = $smItemSellRequest->staff_id;
            }

            if (paymentMethodName($smItemSellRequest->payment_method)) {
                $current_balance_addition = SmItemSell::find($smItemSellRequest->id);
                $item_value = $current_balance_addition->total_paid;

                $bank_value = SmBankAccount::find($smItemSellRequest->bank_id);
                $current_bank_value = $bank_value->current_balance;

                $current_balance = SmBankAccount::find($smItemSellRequest->bank_id);
                $current_balance->current_balance = $current_bank_value - $item_value;
                $current_balance->update();
            }

            $itemSells = SmItemSell::find($smItemSellRequest->id);
            $itemSells->role_id = $smItemSellRequest->role_id;
            $itemSells->student_staff_id = $student_staff_id;
            $itemSells->reference_no = $smItemSellRequest->reference_no;
            $itemSells->sell_date = date('Y-m-d', strtotime($smItemSellRequest->sell_date));
            $itemSells->grand_total = $smItemSellRequest->subTotalValue;
            $itemSells->total_quantity = $smItemSellRequest->subTotalQuantityValue;
            $itemSells->total_paid = $total_paid;
            $itemSells->income_head_id = $smItemSellRequest->income_head_id;
            $itemSells->paid_status = $paid_status;
            $itemSells->total_due = $smItemSellRequest->totalDueValue;
            $itemSells->payment_method = $smItemSellRequest->payment_method;
            $itemSells->description = $smItemSellRequest->description;
            $results = $itemSells->save();
            $itemSells->toArray();

            SmAddIncome::where('item_sell_id', $itemSells->id)->delete();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Item Sell';
            $smAddIncome->date = date('Y-m-d', strtotime($smItemSellRequest->sell_date));
            $smAddIncome->amount = $total_paid;
            $smAddIncome->item_sell_id = $itemSells->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $smItemSellRequest->income_head_id;
            $smAddIncome->payment_method_id = $smItemSellRequest->payment_method;
            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            } else {
                $smAddIncome->academic_id = getAcademicId();
            }

            $smAddIncome->save();

            if (paymentMethodName($smItemSellRequest->payment_method)) {
                SmBankStatement::where('item_sell_id', $itemSells->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->delete();

                $bank = SmBankAccount::where('id', $smItemSellRequest->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $total_paid;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $total_paid;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = 'Item Sell Payment';
                $smBankStatement->item_sell_id = $itemSells->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smItemSellRequest->sell_date));
                $smBankStatement->bank_id = $smItemSellRequest->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smItemSellRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smItemSellRequest->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            if ($results) {
                SmItemSellChild::where('item_sell_id', $itemSells->id)->delete();
                $item_ids = count($smItemSellRequest->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (! empty($smItemSellRequest->item_id[$i])) {
                        $itemSellChild = new SmItemSellChild();
                        $itemSellChild->item_sell_id = $itemSells->id;
                        $itemSellChild->item_id = $smItemSellRequest->item_id[$i];
                        $itemSellChild->sell_price = $smItemSellRequest->unit_price[$i];
                        $itemSellChild->quantity = $smItemSellRequest->quantity[$i];
                        $itemSellChild->sub_total = $smItemSellRequest->totalValue[$i];
                        $itemSellChild->created_by = Auth()->user()->id;
                        $itemSellChild->school_id = Auth::user()->school_id;
                        if (! moduleStatusCheck('University')) {
                            $itemSellChild->academic_id = getAcademicId();
                        }

                        $result = $itemSellChild->save();

                        if ($result) {
                            $items = SmItem::find($smItemSellRequest->item_id[$i]);
                            $items->total_in_stock -= $smItemSellRequest->quantity[$i];
                            if (moduleStatusCheck('University')) {
                                $items->un_academic_id = getAcademicId();
                            } else {
                                $items->academic_id = getAcademicId();
                            }

                            $results = $items->update();
                        }
                    }
                }

            }

            Toastr::success('Operation successful', 'Success');

            return redirect('item-sell-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateItemReceiveData(Request $request, $id)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $request->validate([
            'supplier_id' => 'required',
            'store_id' => 'required',

        ]);
        /*
        try {
        */
            $total_paid = empty($request->totalPaidValue) ? $request->totalPaid : $request->totalPaidValue;
            $subTotalValue = round($request->subTotalValue);
            $totalDueValue = round($request->totalDueValue);

            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }

            $itemReceives = SmItemReceive::find($id);
            $itemReceives->supplier_id = $request->supplier_id;
            $itemReceives->store_id = $request->store_id;
            $itemReceives->reference_no = $request->reference_no;
            $itemReceives->receive_date = date('Y-m-d', strtotime($request->receive_date));
            $itemReceives->grand_total = $request->subTotalValue;
            $itemReceives->total_quantity = $request->subTotalQuantityValue;
            $itemReceives->total_paid = $total_paid;
            $itemReceives->paid_status = $paid_status;
            $itemReceives->total_due = $request->totalDueValue;
            $itemReceives->payment_method = $request->payment_method;
            $results = $itemReceives->update();

            $itemReceiveChildren = SmItemReceiveChild::where('item_receive_id', $id)->delete();

            if ($itemReceiveChildren) {
                $item_ids = count($request->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (! empty($request->item_id[$i])) {
                        $itemReceivedChild = new SmItemReceiveChild;
                        $itemReceivedChild->item_receive_id = $id;
                        $itemReceivedChild->item_id = $request->item_id[$i];
                        $itemReceivedChild->unit_price = $request->unit_price[$i];
                        $itemReceivedChild->quantity = $request->quantity[$i];
                        $itemReceivedChild->sub_total = $request->totalValue[$i];
                        $itemReceivedChild->created_by = Auth()->user()->id;
                        $itemReceivedChild->school_id = Auth::user()->school_id;
                        $itemReceivedChild->academic_id = getAcademicId();
                        $result = $itemReceivedChild->save();

                        if ($result) {
                            $items = SmItem::find($request->item_id[$i]);
                            $items->total_in_stock += $request->quantity[$i];
                            $items->academic_id = getAcademicId();
                            $results = $items->update();
                        }
                    }
                }

                Toastr::success('Operation successful', 'Success');

                return redirect('item-receive-list');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewItemReceive($id)
    {
        /*
        try {
        */
            $viewData = SmItemReceive::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            $editDataChildren = SmItemReceiveChild::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.inventory.viewItemReceive', ['viewData' => $viewData, 'editDataChildren' => $editDataChildren]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function itemSellPayment($id)
    {
        /*
        try {
        */
            $editData = SmItemSell::find($id);

            $sell_heads = SmChartOfAccount::where('active_status', '=', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('type', 'I')
                ->get();

            $paymentDue = SmItemSell::select('total_due')
                ->where('id', $id)
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $paymentMethhods = SmPaymentMethhod::where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();

            return view('backEnd.inventory.itemSellPayment', ['paymentDue' => $paymentDue, 'paymentMethhods' => $paymentMethhods, 'id' => $id, 'sell_heads' => $sell_heads, 'editData' => $editData]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveItemSellPayment(Request $request)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        /*
        try {
        */
            $smInventoryPayment = new SmInventoryPayment();
            $smInventoryPayment->item_receive_sell_id = $request->item_sell_id;
            $smInventoryPayment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $smInventoryPayment->reference_no = $request->reference_no;
            $smInventoryPayment->amount = $request->amount;
            $smInventoryPayment->payment_method = $request->payment_method;
            $smInventoryPayment->notes = $request->notes;
            $smInventoryPayment->payment_type = 'S';
            $smInventoryPayment->created_by = Auth()->user()->id;
            $smInventoryPayment->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smInventoryPayment->un_academic_id = getAcademicId();
            } else {
                $smInventoryPayment->academic_id = getAcademicId();
            }

            $result = $smInventoryPayment->save();

            if (checkAdmin() == true) {
                $itemPaymentDue = SmItemSell::find($request->item_sell_id);
            } else {
                $itemPaymentDue = SmItemSell::where('id', $request->item_sell_id)->where('school_id', Auth::user()->school_id)->first();
            }

            if (isset($itemPaymentDue)) {
                $total_due = $itemPaymentDue->total_due;
                $total_paid = $itemPaymentDue->total_paid;
                $updated_total_due = $total_due - $request->amount;
                $updated_total_paid = $total_paid + $request->amount;
                $itemPaymentDue->total_due = $updated_total_due;
                $itemPaymentDue->total_paid = $updated_total_paid;
                $result = $itemPaymentDue->update();
            }

            // check if full paid
            $itemReceives = SmItemSell::find($request->item_sell_id);
            if ($itemReceives->total_due == 0) {
                $itemReceives->paid_status = 'P';
            }

            // check if Partial paid
            if ($itemReceives->grand_total > $itemReceives->total_due && $itemReceives->total_due > 0) {
                $itemReceives->paid_status = 'PP';
            }

            $results = $itemReceives->update();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Item Sell';
            $smAddIncome->date = date('Y-m-d', strtotime($request->payment_date));
            $smAddIncome->amount = $request->amount;
            $smAddIncome->item_sell_id = $request->item_sell_id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $request->income_head_id;
            $smAddIncome->inventory_id = $smInventoryPayment->id;
            $smAddIncome->payment_method_id = $request->payment_method;
            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            } else {
                $smAddIncome->academic_id = getAcademicId();
            }

            $smAddIncome->save();

            if (paymentMethodName($request->payment_method)) {
                $bank = SmBankAccount::where('id', $request->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $request->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $request->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = 'Item Sell Payment';
                $smBankStatement->item_sell_id = $request->item_sell_id;
                $smBankStatement->item_sell_bank_statement_id = $smInventoryPayment->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $smBankStatement->bank_id = $request->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $request->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($request->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function itemIssueList(Request $request)
    {

        /*
        try {
        */
            $user = Auth::user();
            $roles = InfixRole::where('is_saas', 0)->when((generalSetting()->with_guardian !== 1), function ($query): void {
                $query->where('id', '!=', 3);
            })->where(function ($q) use ($user): void {
                $q->where('school_id', $user->school_id)->orWhere('type', 'System');
            })->select(['id', 'name'])->get();
            $classes = SmClass::where('school_id', $user->school_id)
                ->where('academic_id', getAcademicId())
                ->select(['id', 'class_name'])
                ->get();
            $itemCat = SmItemCategory::where('school_id', $user->school_id)
                ->select(['id', 'category_name'])
                ->get();
            $issuedItems = SmItemIssue::where('active_status', '=', 1)
                ->where('school_id', $user->school_id)
                ->orderby('id', 'DESC')
                ->select(['id', 'role_id', 'issue_date', 'due_date', 'quantity', 'issue_status', 'item_id', 'item_category_id'])
                ->with(['items:item_name,id', 'categories:id,category_name'])
                ->get();

            return view('backEnd.inventory.issueItemList', ['issuedItems' => $issuedItems, 'roles' => $roles, 'classes' => $classes, 'itemCat' => $itemCat]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function getItemByCategory(Request $request)
    {
        $allitems = SmItem::where('item_category_id', '=', $request->id)->where('school_id', Auth::user()->school_id)->get();
        $items = [];
        foreach ($allitems as $allitem) {
            $items[] = SmItem::find($allitem->id);
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($items, null);
        }

        return response()->json([$items]);
    }

    public function saveItemIssueData(SmItemIssueRequest $smItemIssueRequest)
    {
        $items = SmItem::find($smItemIssueRequest->item_id);
        if ($items->total_in_stock < $smItemIssueRequest->quantity) {
            Toastr::error('Quantity can not be greater than stock', 'Failed');

            return redirect()->back();
        }

        /*
        try {
        */
            $issue_to = '';
            if ($smItemIssueRequest->role_id == 2) {
                if (! empty($smItemIssueRequest->student)) {
                    $issue_to = $smItemIssueRequest->student;
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
            } elseif (! empty($smItemIssueRequest->staff_id)) {
                $issue_to = $smItemIssueRequest->staff_id;
            } else {

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }

            $user = Auth()->user();

            $smItemIssue = new SmItemIssue();
            $smItemIssue->role_id = $smItemIssueRequest->role_id;
            $smItemIssue->issue_to = $issue_to;
            $smItemIssue->issue_by = auth()->user()->id;
            $smItemIssue->item_category_id = $smItemIssueRequest->item_category_id;
            $smItemIssue->item_id = $smItemIssueRequest->item_id;
            $smItemIssue->issue_date = date('Y-m-d', strtotime($smItemIssueRequest->issue_date));
            $smItemIssue->due_date = date('Y-m-d', strtotime($smItemIssueRequest->due_date));
            $smItemIssue->quantity = $smItemIssueRequest->quantity;
            $smItemIssue->issue_status = 'I';
            $smItemIssue->note = $smItemIssueRequest->description;
            $smItemIssue->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItemIssue->un_academic_id = getAcademicId();
            } else {
                $smItemIssue->academic_id = getAcademicId();
            }

            $results = $smItemIssue->save();
            $smItemIssue->toArray();
            if ($results) {

                $items = SmItem::find($smItemIssueRequest->item_id);
                $items->total_in_stock -= $smItemIssueRequest->quantity;
                if (moduleStatusCheck('University')) {
                    $items->un_academic_id = getAcademicId();
                }

                $items->update();

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function returnItemView(Request $request, $id)
    {
        /*
        try {
        */
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.inventory.returnItemView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function returnItem(Request $request, $id)
    {
        /*
        try {
        */
            $iuusedItem = SmItemIssue::select('item_id', 'quantity')->where('id', $id)->first();
            $items = SmItem::find($iuusedItem->item_id);
            $items->total_in_stock += $iuusedItem->quantity;
            if (moduleStatusCheck('University')) {
                $items->un_academic_id = getAcademicId();
            } else {
                $items->academic_id = getAcademicId();
            }

            $result = $items->update();
            if ($result) {
                $itemissue = SmItemIssue::find($id);
                $itemissue->issue_status = 'R';
                $itemissue->update();

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewSellPayments($id)
    {

        /*
        try {
        */
            $payments = SmInventoryPayment::with('paymentMethods')->where('item_receive_sell_id', $id)->where('payment_type', 'S')->get();

            return view('backEnd.inventory.viewSellPayments', ['payments' => $payments, 'id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteSellPayment()
    {

        /*
        try {
        */
            $sell_payment_id = $_POST['sell_payment_id'];
            $paymentHistory = SmInventoryPayment::find($sell_payment_id);

            $item_receive_sell_id = $paymentHistory->item_receive_sell_id;
            $amount = $paymentHistory->amount;
            $itemSellData = SmItemSell::find($item_receive_sell_id);
            $itemSellData->total_due += $amount;
            $itemSellData->total_paid -= $amount;

            if (paymentMethodName($itemSellData->payment_method)) {
                $bank = SmBankAccount::where('id', $itemSellData->account_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $amount;

                $current_balance = SmBankAccount::find($itemSellData->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();

                $delete_balance = SmBankStatement::where('item_sell_id', $itemSellData->id)
                    ->where('item_sell_bank_statement_id', $paymentHistory->id)
                    ->where('amount', $amount)
                    ->delete();
            }

            SmAddIncome::where('inventory_id', $paymentHistory->id)->delete();

            // check if total due is greater than 0
            if (($itemSellData->total_due + $amount) > 0) {
                $itemSellData->paid_status = 'PP';
            }

            // check if total due is equal to 0
            if (($itemSellData->total_due + $amount) == 0) {
                $itemSellData->paid_status = 'P';
            }

            $itemSellData->update();

            SmInventoryPayment::destroy($sell_payment_id);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function cancelItemSellView($id)
    {

        /*
        try {
        */
            return view('backEnd.inventory.cancelItemSellView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function cancelItemSell($id)
    {
        /*
        try {
        */
            $itemSell = SmItemSell::find($id);
            $itemSell->paid_status = 'S';
            $results = $itemSell->update();
            SmAddIncome::where('item_sell_id', $itemSell->id)
                ->where('school_id', Auth::user()->school_id)
                ->delete();

            if (paymentMethodName($itemSell->payment_method)) {
                $reset_balance = SmBankStatement::where('item_sell_id', $itemSell->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('amount');

                $bank = SmBankAccount::where('id', $itemSell->account_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $reset_balance;

                $current_balance = SmBankAccount::find($itemSell->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();

                SmBankStatement::where('item_sell_id', $itemSell->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->delete();
            }

            if ($results) {
                $itemSellChild = SmItemSellChild::where('item_sell_id', $id)->where('school_id', Auth::user()->school_id)->get();
                if (! empty($itemSellChild)) {
                    foreach ($itemSellChild as $value) {
                        $items = SmItem::find($value->item_id);
                        $items->total_in_stock += $value->quantity;
                        $result = $items->update();
                    }
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
