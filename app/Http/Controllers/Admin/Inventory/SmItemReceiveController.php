<?php

namespace App\Http\Controllers\Admin\Inventory;

use Exception;
use App\SmItem;
use App\SmItemSell;
use App\SmSupplier;
use App\SmItemStore;
use App\SmAddExpense;
use App\SmBankAccount;
use App\SmItemReceive;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use App\SmInventoryPayment;
use App\SmItemReceiveChild;
use Illuminate\Http\Request;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Requests\Admin\Inventory\SmItemReceiveRequest;

class SmItemReceiveController extends Controller
{
    use NotificationSend;

    // This is for upadate database sm_item_receive_children table for the issue of  float/double datatype only stores 8 digits.

    public static function updateSmItemReceiveChildrenDatabase(): ?string
    {
        try {
            Schema::table('sm_item_receive_children', function (Blueprint $blueprint): void {
                $blueprint->decimal('unit_price', 20, 2)->change();
                $blueprint->decimal('quantity', 20, 2)->change();
                $blueprint->decimal('sub_total', 20, 2)->change();
            });
        } catch (Exception $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    public function itemReceive()
    {
        /*
        try {
        */
            $user = Auth::user();
            $account_id = SmBankAccount::where('school_id', $user->school_id)->select(['id', 'bank_name', 'account_name', 'account_number', 'opening_balance', 'current_balance'])->get();
            $expense_head = SmChartOfAccount::where('type', 'E')->select(['id', 'head', 'type'])->get();
            $suppliers = SmSupplier::select(['id', 'company_name'])->get();
            $itemStores = SmItemStore::where('school_id', $user->school_id)->select(['store_name', 'id'])->get();
            $items = SmItem::with('category:id,category_name')->select(['item_name', 'id'])->get();
            $paymentMethhods = SmPaymentMethhod::select(['method', 'id'])->get();

            return view('backEnd.inventory.itemReceive', ['suppliers' => $suppliers, 'itemStores' => $itemStores, 'items' => $items, 'paymentMethhods' => $paymentMethhods, 'account_id' => $account_id, 'expense_head' => $expense_head]);
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
            $searchData = SmItem::where('school_id', Auth::user()->school_id)->get();
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

    public function saveItemReceiveData(SmItemReceiveRequest $smItemReceiveRequest)
    {
       //   uest->all());
       // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        /*
        try {
        */
            $total_paid = empty($smItemReceiveRequest->totalPaidValue) ? $smItemReceiveRequest->totalPaid : $smItemReceiveRequest->totalPaidValue;
            $subTotalValue = round($smItemReceiveRequest->subTotalValue);
            $totalDueValue = round($smItemReceiveRequest->totalDueValue);
            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }
            // dd($paid_status);
            $smItemReceive = new SmItemReceive();
            $smItemReceive->supplier_id = $smItemReceiveRequest->supplier_id;
            $smItemReceive->store_id = $smItemReceiveRequest->store_id;
            $smItemReceive->reference_no = $smItemReceiveRequest->reference_no;
            $smItemReceive->receive_date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
            $smItemReceive->grand_total = $smItemReceiveRequest->subTotalValue;
            $smItemReceive->total_quantity = $smItemReceiveRequest->subTotalQuantityValue;
            $smItemReceive->total_paid = $total_paid;
            $smItemReceive->paid_status = $paid_status;
            $smItemReceive->total_due = $smItemReceiveRequest->totalDueValue;
            $smItemReceive->account_id = $smItemReceiveRequest->bank_id;
            $smItemReceive->expense_head_id = $smItemReceiveRequest->expense_head_id;
            $smItemReceive->payment_method = $smItemReceiveRequest->payment_method;
            $smItemReceive->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItemReceive->un_academic_id = getAcademicId();
            } else {
                $smItemReceive->academic_id = getAcademicId();
            }

            $results = $smItemReceive->save();
            $smItemReceive->toArray();

            $smAddExpense = new SmAddExpense();
            $smAddExpense->name = 'Item Receive';
            $smAddExpense->date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
            $smAddExpense->amount = $total_paid;
            $smAddExpense->item_receive_id = $smItemReceive->id;
            $smAddExpense->active_status = 1;
            $smAddExpense->expense_head_id = $smItemReceiveRequest->expense_head_id;
            $smAddExpense->account_id = $smItemReceiveRequest->bank_id;
            $smAddExpense->payment_method_id = $smItemReceiveRequest->payment_method;
            $smAddExpense->created_by = Auth()->user()->id;
            $smAddExpense->school_id = Auth::user()->school_id;

            if (moduleStatusCheck('University')) {
                $smAddExpense->un_academic_id = getAcademicId();
            } else {
                $smAddExpense->academic_id = getAcademicId();
            }

            $smAddExpense->save();

            if (paymentMethodName($smItemReceiveRequest->payment_method)) {
                $bank = SmBankAccount::where('id', $smItemReceiveRequest->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $total_paid;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $total_paid;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 0;
                $smBankStatement->details = 'Item Receive Payment';
                $smBankStatement->item_receive_id = $smItemReceive->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
                $smBankStatement->bank_id = $smItemReceiveRequest->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smItemReceiveRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smItemReceiveRequest->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            $itemName = [];

            if ($results) {
                $item_ids = count($smItemReceiveRequest->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (! empty($smItemReceiveRequest->item_id[$i])) {
                        $itemReceivedChild = new SmItemReceiveChild;
                        $itemReceivedChild->item_receive_id = $smItemReceive->id;
                        $itemReceivedChild->item_id = $smItemReceiveRequest->item_id[$i];
                        $itemReceivedChild->unit_price = $smItemReceiveRequest->unit_price[$i];
                        $itemReceivedChild->quantity = $smItemReceiveRequest->quantity[$i];
                        $itemReceivedChild->sub_total = $smItemReceiveRequest->totalValue[$i];
                        $itemReceivedChild->created_by = Auth()->user()->id;
                        if (! moduleStatusCheck('University')) {
                            $itemReceivedChild->academic_id = getAcademicId();
                        }
                        $itemReceivedChild->school_id = Auth::user()->school_id;
                        $result = $itemReceivedChild->save();
                        $itemName[] = $itemReceivedChild->items->item_name;

                        if ($result) {
                            $items = SmItem::find($smItemReceiveRequest->item_id[$i]);
                            $items->total_in_stock += $smItemReceiveRequest->quantity[$i];
                            $results = $items->update();
                        }
                    }
                }
            }

            $data['title'] = 'Item Receive';
            $data['grand_total'] = $smItemReceiveRequest->subTotalValue;
            $data['total_paid'] = $total_paid;
            $data['total_due'] = $smItemReceiveRequest->totalDueValue;
            $data['quantity'] = $smItemReceiveRequest->subTotalQuantityValue;
            $data['item'] = implode(', ', $itemName);
            $this->sent_notifications('Item_Recieved', [auth()->user()->id], $data, ['1']);

            Toastr::success('Operation successful', 'Success');

            return redirect('item-receive-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function itemReceiveList()
    {
        /*
        try {
        */
            $allItemReceiveLists = SmItemReceive::with('suppliers', 'paymentMethodName', 'bankName')
                ->get();

            return view('backEnd.inventory.itemReceiveList', ['allItemReceiveLists' => $allItemReceiveLists]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function editItemReceive(Request $request, $id)
    {
        /*
        try {
        */
            $expense_head = SmChartOfAccount::where('type', 'E')->select(['id', 'head', 'type'])->get();

            $account_id = SmBankAccount::where('school_id', Auth::user()->school_id)->select(['id', 'bank_name', 'account_name', 'account_number', 'opening_balance', 'current_balance'])->get();

            $editData = SmItemReceive::find($id);

            $editDataChildren = SmItemReceiveChild::with('items')->where('item_receive_id', $id)->get();

            $suppliers = SmSupplier::select(['id', 'company_name'])->get();

            $itemStores = SmItemStore::select(['store_name', 'id'])->get();

            $items = SmItem::select(['item_name', 'id'])->get();

            $paymentMethhods = SmPaymentMethhod::where('id', '!=', 3)->select(['method', 'id'])->get();

            return view('backEnd.inventory.editItemReceive', ['editData' => $editData, 'editDataChildren' => $editDataChildren, 'suppliers' => $suppliers, 'itemStores' => $itemStores, 'items' => $items, 'paymentMethhods' => $paymentMethhods, 'expense_head' => $expense_head, 'account_id' => $account_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateItemReceiveData(SmItemReceiveRequest $smItemReceiveRequest, $id)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        /*
        try {
        */
            $total_paid = empty($smItemReceiveRequest->totalPaidValue) ? $smItemReceiveRequest->totalPaid : $smItemReceiveRequest->totalPaidValue;
            $subTotalValue = round($smItemReceiveRequest->subTotalValue);
            $totalDueValue = round($smItemReceiveRequest->totalDueValue);
            $paid_status = '';
            if ($totalDueValue == 0) {
                $paid_status = 'P';
            } elseif ($subTotalValue == $totalDueValue) {
                $paid_status = 'U';
            } else {
                $paid_status = 'PP';
            }

            if (paymentMethodName($smItemReceiveRequest->payment_method)) {
                $current_balance_subtraction = SmItemReceive::find($id);
                $item_value = $current_balance_subtraction->total_paid;

                $bank_value = SmBankAccount::find($smItemReceiveRequest->bank_id);
                $current_bank_value = $bank_value->current_balance;

                $current_balance = SmBankAccount::find($smItemReceiveRequest->bank_id);
                $current_balance->current_balance = $current_bank_value + $item_value;
                $current_balance->update();
            }

            $itemReceives = SmItemReceive::find($id);
            $itemReceives->supplier_id = $smItemReceiveRequest->supplier_id;
            $itemReceives->store_id = $smItemReceiveRequest->store_id;
            $itemReceives->reference_no = $smItemReceiveRequest->reference_no;
            $itemReceives->receive_date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
            $itemReceives->grand_total = $smItemReceiveRequest->subTotalValue;
            $itemReceives->total_quantity = $smItemReceiveRequest->subTotalQuantityValue;
            $itemReceives->total_paid = $total_paid;
            $itemReceives->paid_status = $paid_status;
            $itemReceives->expense_head_id = $smItemReceiveRequest->expense_head_id;
            $itemReceives->total_due = $smItemReceiveRequest->totalDueValue;
            $itemReceives->payment_method = $smItemReceiveRequest->payment_method;
            $results = $itemReceives->update();

            SmAddExpense::where('item_receive_id', $itemReceives->id)->delete();

            $smAddExpense = new SmAddExpense();
            $smAddExpense->name = 'Item Receive';
            $smAddExpense->date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
            $smAddExpense->amount = $total_paid;
            $smAddExpense->item_receive_id = $itemReceives->id;
            $smAddExpense->active_status = 1;
            $smAddExpense->expense_head_id = $smItemReceiveRequest->expense_head_id;
            $smAddExpense->payment_method_id = $smItemReceiveRequest->payment_method;
            $smAddExpense->created_by = Auth()->user()->id;
            $smAddExpense->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddExpense->un_academic_id = getAcademicId();
            } else {
                $smAddExpense->academic_id = getAcademicId();
            }

            $smAddExpense->save();

            if (paymentMethodName($smItemReceiveRequest->payment_method)) {
                SmBankStatement::where('item_receive_id', $itemReceives->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->delete();

                $bank = SmBankAccount::where('id', $smItemReceiveRequest->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $total_paid;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $total_paid;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 0;
                $smBankStatement->details = 'Item Receive Payment';
                $smBankStatement->item_receive_id = $itemReceives->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smItemReceiveRequest->receive_date));
                $smBankStatement->bank_id = $smItemReceiveRequest->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smItemReceiveRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smItemReceiveRequest->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            if ($results) {
                $allItemReceiveChildren = SmItemReceiveChild::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->get();
                foreach ($allItemReceiveChildren as $allItemReceiveChild) {
                    $items = SmItem::find($allItemReceiveChild->item_id);
                    $items->total_in_stock -= $allItemReceiveChild->quantity;
                    $results = $items->update();
                }
            }

            $itemReceiveChildren = SmItemReceiveChild::where('item_receive_id', $id)->delete();

            if ($itemReceiveChildren) {
                $item_ids = count($smItemReceiveRequest->item_id);
                for ($i = 0; $i < $item_ids; $i++) {
                    if (! empty($smItemReceiveRequest->item_id[$i])) {
                        $itemReceivedChild = new SmItemReceiveChild;
                        $itemReceivedChild->item_receive_id = $id;
                        $itemReceivedChild->item_id = $smItemReceiveRequest->item_id[$i];
                        $itemReceivedChild->unit_price = $smItemReceiveRequest->unit_price[$i];
                        $itemReceivedChild->quantity = $smItemReceiveRequest->quantity[$i];
                        $itemReceivedChild->sub_total = $smItemReceiveRequest->totalValue[$i];
                        $itemReceivedChild->created_by = Auth()->user()->id;
                        $itemReceivedChild->school_id = Auth::user()->school_id;
                        if (! moduleStatusCheck('University')) {
                            $itemReceivedChild->academic_id = getAcademicId();
                        }

                        $result = $itemReceivedChild->save();

                        if ($result) {
                            $items = SmItem::find($smItemReceiveRequest->item_id[$i]);
                            $items->total_in_stock += $smItemReceiveRequest->quantity[$i];
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
            $general_setting = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();
            $viewData = SmItemReceive::find($id);
            $editDataChildren = SmItemReceiveChild::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.inventory.viewItemReceive', ['viewData' => $viewData, 'editDataChildren' => $editDataChildren, 'general_setting' => $general_setting]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function itemReceivePayment($id)
    {
        /*
        try {
        */
            $paymentDue = SmItemReceive::select('total_due')->where('id', $id)->first();

            $editData = SmItemReceive::find($id);

            $paymentMethhods = SmPaymentMethhod::where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();
            $account_id = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            $expense_head = SmChartOfAccount::where('active_status', '=', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('type', 'E')
                ->get();

            return view('backEnd.inventory.itemReceivePayment', ['paymentDue' => $paymentDue, 'paymentMethhods' => $paymentMethhods, 'id' => $id, 'expense_head' => $expense_head, 'editData' => $editData, 'account_id' => $account_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveItemReceivePayment(Request $request)
    {
        //  DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        /*
        try {
        */
            $smInventoryPayment = new SmInventoryPayment();
            $smInventoryPayment->item_receive_sell_id = $request->item_receive_id;
            $smInventoryPayment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $smInventoryPayment->reference_no = $request->reference_no;
            $smInventoryPayment->amount = $request->amount;
            $smInventoryPayment->payment_method = $request->payment_method;
            $smInventoryPayment->notes = $request->notes;
            $smInventoryPayment->payment_type = 'R';
            $smInventoryPayment->created_by = Auth()->user()->id;
            $smInventoryPayment->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smInventoryPayment->un_academic_id = getAcademicId();
            } else {
                $smInventoryPayment->academic_id = getAcademicId();
            }

            $result = $smInventoryPayment->save();

            $itemPaymentDue = SmItemReceive::find($request->item_receive_id);
            if (isset($itemPaymentDue)) {
                $total_due = $itemPaymentDue->total_due;
                $total_paid = $itemPaymentDue->total_paid;
                $updated_total_due = $total_due - $request->amount;
                $updated_total_paid = $total_paid + $request->amount;
                $itemPaymentDue->total_due = $updated_total_due;
                $itemPaymentDue->total_paid = $updated_total_paid;
                if (moduleStatusCheck('University')) {
                    $itemPaymentDue->un_academic_id = getAcademicId();
                } else {
                    $itemPaymentDue->academic_id = getAcademicId();
                }

                $result = $itemPaymentDue->update();

                $smAddExpense = new SmAddExpense();
                $smAddExpense->name = 'Item Receive';
                $smAddExpense->date = date('Y-m-d', strtotime($request->payment_date));
                $smAddExpense->amount = $request->amount;
                $smAddExpense->item_receive_id = $request->item_receive_id;
                $smAddExpense->active_status = 1;
                $smAddExpense->expense_head_id = $request->expense_head_id;
                $smAddExpense->inventory_id = $smInventoryPayment->id;
                $smAddExpense->payment_method_id = $request->payment_method;
                $smAddExpense->created_by = Auth()->user()->id;
                $smAddExpense->school_id = Auth::user()->school_id;
                if (moduleStatusCheck('University')) {
                    $smAddExpense->un_academic_id = getAcademicId();
                } else {
                    $smAddExpense->academic_id = getAcademicId();
                }

                $smAddExpense->save();

                if (paymentMethodName($request->payment_method)) {
                    $bank = SmBankAccount::where('id', $request->bank_id)
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    $after_balance = $bank->current_balance - $request->amount;

                    $smBankStatement = new SmBankStatement();
                    $smBankStatement->amount = $request->amount;
                    $smBankStatement->after_balance = $after_balance;
                    $smBankStatement->type = 0;
                    $smBankStatement->details = 'Item Receive Payment';
                    $smBankStatement->item_receive_id = $request->item_receive_id;
                    $smBankStatement->item_receive_bank_statement_id = $smInventoryPayment->id;
                    $smBankStatement->payment_date = date('Y-m-d', strtotime($request->payment_date));
                    $smBankStatement->bank_id = $request->bank_id;
                    $smBankStatement->school_id = Auth::user()->school_id;
                    $smBankStatement->payment_method = $request->payment_method;
                    $smBankStatement->save();
                    $current_balance = SmBankAccount::find($request->bank_id);
                    $current_balance->current_balance = $after_balance;
                    $current_balance->update();
                }

            }

            // check if full paid
            $itemReceives = SmItemReceive::find($request->item_receive_id);
            if ($itemReceives->total_due == 0) {
                $itemReceives->paid_status = 'P';
            }

            // check if Partial paid
            if ($itemReceives->grand_total > $itemReceives->total_due && $itemReceives->total_due > 0) {
                $itemReceives->paid_status = 'PP';
            }

            $results = $itemReceives->update();

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

    public function viewReceivePayments($id)
    {

        /*
        try {
        */
            $payments = SmInventoryPayment::where('item_receive_sell_id', $id)->where('payment_type', 'R')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.inventory.viewReceivePayments', ['payments' => $payments, 'id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteReceivePayment()
    {
        /*
        try {
        */
            $receive_payment_id = $_POST['receive_payment_id'];
            $paymentHistory = SmInventoryPayment::find($receive_payment_id);
            $item_receive_sell_id = $paymentHistory->item_receive_sell_id;
            $amount = $paymentHistory->amount;

            $itemReceivesData = SmItemReceive::find($item_receive_sell_id);
            $itemReceivesData->total_due += $amount;
            $itemReceivesData->total_paid -= $amount;

            if (paymentMethodName($itemReceivesData->payment_method)) {
                $bank = SmBankAccount::where('id', $itemReceivesData->account_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $amount;

                $current_balance = SmBankAccount::find($itemReceivesData->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();

                $delete_balance = SmBankStatement::where('item_receive_id', $itemReceivesData->id)
                    ->where('item_receive_bank_statement_id', $paymentHistory->id)
                    ->where('amount', $amount)
                    ->delete();
            }

            $delete_expense = SmAddExpense::where('inventory_id', $paymentHistory->id)->delete();

            // check if total due is greater than 0
            if (($itemReceivesData->total_due + $amount) > 0) {
                $itemReceivesData->paid_status = 'PP';
            }

            // check if total due is equal to 0
            if (($itemReceivesData->total_due + $amount) == 0) {
                $itemReceivesData->paid_status = 'P';
            }

            $itemReceivesData->update();
            $result = SmInventoryPayment::destroy($receive_payment_id);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemReceiveView(string $id)
    {
        /*
        try {
        */
            $title = 'Are you sure to detete this Receive item?';
            $url = url('delete-item-receive/'.$id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemSaleView(string $id)
    {
        /*
        try {
        */
            $title = 'Are you sure to detete this Sale item?';
            $url = url('delete-item-sale/'.$id);
            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemReceive($id)
    {
        /*
        try {
        */
            $itemReceivedChilds = SmItemReceiveChild::where('item_receive_id', $id)
                                                    ->where('school_id', Auth::user()->school_id)
                                                    ->get();
            foreach ($itemReceivedChilds as $itemReceivedChild) {
                $items = SmItem::where('id', $itemReceivedChild->item_id)->where('school_id', Auth::user()->school_id)->first();
                $items->total_in_stock -= $itemReceivedChild->quantity;
                $results = $items->update();
                $iReceChi = SmItemReceiveChild::where('id', $itemReceivedChild->id)->where('school_id', Auth::user()->school_id)->delete();
            }
            $result = SmItemReceive::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
            $delete_expense = SmAddExpense::where('item_receive_id', $id)->where('school_id', Auth::user()->school_id)->delete();
            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $queryException) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemSale($id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('item_sell_id', $id);
            /*
            try {
            */
                $result = SmItemSell::destroy($id);
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            /*
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function cancelItemReceiveView($id)
    {

        /*
        try {
        */
            return view('backEnd.inventory.cancelItemReceiveView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function cancelItemReceive($id)
    {
        /*
        try {
        */
            $itemReceives = SmItemReceive::find($id);
            $itemReceives->paid_status = 'R';
            $results = $itemReceives->update();

            $itemReceives->expnese_head_id;
            $refund = SmAddExpense::where('item_receive_id', $itemReceives->id)
                ->where('school_id', Auth::user()->school_id)
                ->delete();

            if (paymentMethodName($itemReceives->payment_method)) {
                $reset_balance = SmBankStatement::where('item_receive_id', $itemReceives->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('amount');
                $bank = SmBankAccount::where('id', $itemReceives->account_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $reset_balance;
                $current_balance = SmBankAccount::find($itemReceives->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
                $delete_balance = SmBankStatement::where('item_receive_id', $itemReceives->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->delete();
            }

            if ($results) {
                $itemReceiveChild = SmItemReceiveChild::where('item_receive_id', $id)
                                                       ->where('school_id', Auth::user()->school_id)
                                                       ->get();

                if (! empty($itemReceiveChild)) {
                    foreach ($itemReceiveChild as $value) {
                        $items = SmItem::find($value->item_id);
                        $items->total_in_stock -= $value->quantity;
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
    
    # This is for upadate database sm_item_receive_children table for the issue of  float/double datatype only stores 8 digits.

    public static function updateSmItemReceiveDatabase()
    {
        /*
        try {
        */
            Schema::table('sm_item_receives', function (Blueprint $table) {
                $table->decimal('grand_total', 20, 2)->change();
                $table->decimal('total_quantity', 20, 2)->change();
                $table->decimal('total_paid', 20, 2)->change();
                $table->decimal('total_due', 20, 2)->change();
            });
        /*
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        */
    }
}
