<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use Exception;
use App\SmClass;
use App\SmParent;
use App\SmStudent;
use App\SmAddIncome;
use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmBankAccount;
use App\SmFeesPayment;
use DateTimeImmutable;
use App\SmFeesDiscount;
use App\SmBankStatement;
use App\SmPaymentMethhod;
use App\Models\FeesInvoice;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\SmFeesAssignDiscount;
use Barryvdh\DomPDF\Facade\Pdf;
use App\SmPaymentGatewaySetting;
use App\Models\DirectFeesSetting;
use App\Models\DirectFeesReminder;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectFeesInstallmentAssign;
use Modules\BulkPrint\Entities\InvoiceSetting;
use App\Models\DireFeesInstallmentChildPayment;
use Modules\University\Entities\UnFeesInstallmentAssign;
use App\Http\Requests\Admin\Accounts\SmFineReportSearchRequest;
use Modules\University\Entities\UnFeesInstallAssignChildPayment;

class SmFeesController extends Controller
{


    public function feesGenerateModal(Request $request, $amount, $student_id, $type, $master, $assign_id, $record_id)
    {
        /*
        try {
        */
            $amount /= 100;
            $fees_type_id = $type;

            $banks = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)
                ->where('record_id', $record_id)
                ->where('fees_type_id', $fees_type_id)
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return view('backEnd.feesCollection.fees_generate_modal', ['amount' => $amount, 'assign_id' => $assign_id, 'master' => $master, 'discounts' => $discounts, 'fees_type_id' => $fees_type_id, 'student_id' => $student_id, 'data' => $data, 'method' => $method, 'banks' => $banks, 'record_id' => $record_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesGenerateModalChild(Request $request, $amount, $student_id, $type)
    {
        /*
        try {
        */
            $amount /= 100;
            $fees_type_id = $type;
            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($discounts as $discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status', 1)->where('fees_discount_id', $discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            return view('backEnd.feesCollection.fees_generate_modal_child', ['amount' => $amount, 'discounts' => $discounts, 'fees_type_id' => $fees_type_id, 'student_id' => $student_id, 'applied_discount' => $applied_discount]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesPaymentStore(Request $request)
    {
        if (db_engine() !== 'pgsql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        
        try {
        
            $fileName = '';
            if ($request->file('slip') !== '') {
                $file = $request->file('slip');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/bankSlip/', $fileName);
                $fileName = 'public/uploads/bankSlip/'.$fileName;
            }

            $discount_group = explode('-', $request->discount_group);
            $user = Auth::user();
            $smFeesPayment = new SmFeesPayment();
            $smFeesPayment->student_id = $request->student_id;
            $smFeesPayment->fees_discount_id = empty($request->fees_discount_id) ? null : $request->fees_discount_id;
            $smFeesPayment->discount_amount = empty($request->applied_amount) ? 0 : $request->applied_amount;
            $smFeesPayment->fine = empty($request->fine) ? 0 : $request->fine;
            $smFeesPayment->assign_id = $request->assign_id;
            $smFeesPayment->amount = empty($request->amount) ? 0 : $request->amount;
            $smFeesPayment->assign_id = $request->assign_id;
            $smFeesPayment->payment_date = date('Y-m-d', strtotime($request->date));
            $smFeesPayment->payment_mode = $request->payment_mode;
            $smFeesPayment->created_by = $user->id;
            $smFeesPayment->note = $request->note;
            $smFeesPayment->fine_title = $request->fine_title;
            $smFeesPayment->school_id = Auth::user()->school_id;
            $smFeesPayment->slip = $fileName;
            $smFeesPayment->record_id = $request->record_id;
            $smFeesPayment->academic_id = getAcademicid();
            if (moduleStatusCheck('University')) {
                $smFeesPayment->un_academic_id = getAcademicId();
                $smFeesPayment->un_fees_installment_id = $request->installment_id;
                $smFeesPayment->un_semester_label_id = $request->un_semester_label_id;
                $installment = UnFeesInstallmentAssign::find($smFeesPayment->un_fees_installment_id);
                $payable_amount = discountFeesAmount($installment->id);
                $sub_payment = $installment->payments->sum('paid_amount');
                $direct_payment = $installment->paid_amount;
                $total_paid = $sub_payment + $direct_payment;
                $installment->payment_date = date('Y-m-d', strtotime($request->date));

                $last_inovoice = UnFeesInstallAssignChildPayment::where('school_id', auth()->user()->school_id)->max('invoice_no');
                $new_subPayment = new UnFeesInstallAssignChildPayment();
                $new_subPayment->un_fees_installment_assign_id = $installment->id;
                $new_subPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                $new_subPayment->amount = $request->amount;
                $new_subPayment->paid_amount = $request->amount;
                $new_subPayment->payment_date = $smFeesPayment->payment_date;
                $new_subPayment->payment_mode = $smFeesPayment->payment_mode;
                $new_subPayment->note = $request->note;
                $new_subPayment->slip = $fileName;
                $new_subPayment->active_status = 1;
                $new_subPayment->bank_id = $request->bank_id;
                $new_subPayment->discount_amount = 0;
                $new_subPayment->fees_type_id = $installment->fees_type_id;
                $new_subPayment->student_id = $request->student_id;
                $new_subPayment->record_id = $request->record_id;
                $new_subPayment->un_semester_label_id = $request->un_semester_label_id;
                $new_subPayment->un_academic_id = getAcademicId();
                $new_subPayment->created_by = Auth::user()->id;
                $new_subPayment->updated_by = Auth::user()->id;
                $new_subPayment->school_id = Auth::user()->school_id;
                $new_subPayment->balance_amount = ($payable_amount - ($sub_payment + $request->amount));
                $new_subPayment->save();
                $smFeesPayment->installment_payment_id = $new_subPayment->id;

                $installment->active_status = ($sub_payment + $request->amount) == $payable_amount ? 1 : 2;
                $installment->paid_amount = $sub_payment + $request->amount;
                $installment->save();

            } elseif (directFees()) {
                $installment = DirectFeesInstallmentAssign::find($request->installment_id);
                $payable_amount = discountFees($installment->id);
                $sub_payment = $installment->payments->sum('paid_amount');
                $direct_payment = $installment->paid_amount;
                $total_paid = $sub_payment + $direct_payment;
                $smFeesPayment->direct_fees_installment_assign_id = $installment->id;
                $smFeesPayment->academic_id = getAcademicId();

                $last_inovoice = DireFeesInstallmentChildPayment::where('school_id', auth()->user()->school_id)->max('invoice_no');
                $new_subPayment = new DireFeesInstallmentChildPayment();
                $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                $new_subPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                $new_subPayment->amount = $request->amount;
                $new_subPayment->paid_amount = $request->amount;
                $new_subPayment->payment_date = $smFeesPayment->payment_date;
                $new_subPayment->payment_mode = $smFeesPayment->payment_mode;
                $new_subPayment->note = $request->note;
                $new_subPayment->slip = $fileName;
                $new_subPayment->active_status = 1;
                $new_subPayment->bank_id = $request->bank_id;
                $new_subPayment->discount_amount = 0;
                $new_subPayment->fees_type_id = $installment->fees_type_id;
                $new_subPayment->student_id = $request->student_id;
                $new_subPayment->record_id = $request->record_id;

                $new_subPayment->created_by = Auth::user()->id;
                $new_subPayment->updated_by = Auth::user()->id;
                $new_subPayment->school_id = Auth::user()->school_id;
                $new_subPayment->balance_amount = ($payable_amount - ($sub_payment + $request->amount));
                $new_subPayment->save();
                $smFeesPayment->installment_payment_id = $new_subPayment->id;

                $installment->active_status = ($sub_payment + $request->amount) == $payable_amount ? 1 : 2;
                $installment->paid_amount = $sub_payment + $request->amount;
                $installment->save();
                // }

            } else {
                $smFeesPayment->fees_type_id = $request->fees_type_id;
                $smFeesPayment->academic_id = getAcademicId();
            }

            $result = $smFeesPayment->save();
            $payment_mode_name = ucwords($request->payment_mode);
            $payment_method = SmPaymentMethhod::where('method', $payment_mode_name)->first();
            $income_head = generalSetting();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Fees Collect';
            $smAddIncome->date = date('Y-m-d', strtotime($request->date));
            $smAddIncome->amount = $smFeesPayment->amount;
            $smAddIncome->fees_collection_id = $smFeesPayment->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $income_head->income_head_id;
            $smAddIncome->payment_method_id = $payment_method->id;
            $smAddIncome->account_id = $request->bank_id;
            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            }

            $smAddIncome->academic_id = getAcademicId();
            $smAddIncome->save();

            if ($payment_method->id == 3) {
                $bank = SmBankAccount::where('id', $request->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $request->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $request->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = 'Fees Payment';
                $smBankStatement->payment_date = date('Y-m-d', strtotime($request->date));
                $smBankStatement->bank_id = $request->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $payment_method->id;
                $smBankStatement->fees_payment_id = $smFeesPayment->id;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($request->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            if (moduleStatusCheck('University')) {
                $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)
                    ->where('student_id', $request->student_id)
                    ->where('record_id', $request->record_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();

            } elseif (directFees()) {
                $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)
                    ->where('student_id', $request->student_id)
                    ->where('record_id', $request->record_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            } else {
                $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)
                    ->where('student_id', $request->student_id)
                    ->where('record_id', $request->record_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $fees_assign->fees_amount -= (float) ($request->amount);
                $fees_assign->save();

                if (! empty($request->fine)) {
                    $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)
                        ->where('student_id', $request->student_id)
                        ->where('record_id', $request->record_id)
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    $fees_assign->fees_amount += $request->fine;
                    $fees_assign->save();
                }

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
                // return Redirect::route('fees_collect_student_wise', array('id' => $request->record_id));
            }

            Toastr::error('Operation Failed', 'Failed');
            return Redirect::route('fees_collect_student_wise', ['id' => $request->record_id]);

        
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        
    }

    public function feesPaymentDelete(Request $request)
    {
        /*
        try {
        */
            $assignFee = SmFeesAssign::find($request->assign_id);
            if ($assignFee) {
                $newAmount = $assignFee->fees_amount + $request->amount;
                $assignFee->fees_amount = $newAmount;
                $assignFee->save();
            }

            if (checkAdmin() == true) {
                $payment = SmFeesPayment::find($request->id);
            } else {
                $payment = SmFeesPayment::where('active_status', 1)->where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            if ($payment) {
                $income = SmAddIncome::where('fees_collection_id', $payment->id)->first();
                if ($income) {
                    $income->delete();
                }
            }

            $result = $payment->delete();

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

    public function searchFeesDueCopy(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $fees_masters = SmFeesMaster::select('fees_group_id')
                ->where('active_status', 1)
                ->distinct('fees_group_id')
                ->where('school_id', Auth::user()->school_id)
                ->when(moduleStatusCheck('University'), function ($q): void {
                    $q->where('un_academic_id', getAcademicId());
                })
                ->when(! moduleStatusCheck('University'), function ($q): void {
                    $q->where('academic_id', getAcademicId());
                })
                ->get();

            $students = StudentRecord::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $fees_dues = [];
            $dues_fees_master = SmFeesMaster::select('id', 'amount', 'date')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
            foreach ($students as $student) {
                foreach ($dues_fees_master as $due_fee_master) {
                    $total_amount = @$due_fee_master->amount;
                    $fees_assign = SmFeesAssign::where('student_id', $student->student_id)
                        ->where('record_id', $student->id)
                        ->where('fees_master_id', @$due_fee_master->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->first();

                    $discount_amount = SmFeesAssign::where('student_id', $student->student_id)
                        ->where('record_id', $student->id)
                        ->where('academic_id', getAcademicId())
                        ->where('fees_master_id', @$due_fee_master->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->sum('applied_discount');

                    $amount = SmFeesPayment::where('active_status', 1)
                        ->where('student_id', $student->student_id)
                        ->where('record_id', $student->id)
                        ->where('academic_id', getAcademicId())
                        ->sum('amount');

                    $paid = $discount_amount + $amount;

                    if ($fees_assign !== '' && $total_amount > $paid) {
                        $due_date = strtotime($due_fee_master->date);
                        $now = strtotime(date('Y-m-d'));
                        if ($due_date > $now) {
                            continue;
                        }

                        $fees_dues[] = $fees_assign;
                    }
                }
            }

            return view('backEnd.feesCollection.search_fees_due', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_dues' => $fees_dues]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function universitySearchFeesDue($request)
    {
        /*
        try {
        */

            $fees_masters = SmFeesMaster::select('fees_group_id')
                ->where('active_status', 1)
                ->distinct('fees_group_id')
                ->where('school_id', Auth::user()->school_id)
                ->when(moduleStatusCheck('University'), function ($q): void {
                    $q->where('un_academic_id', getAcademicId());
                })
                ->when(! moduleStatusCheck('University'), function ($q): void {
                    $q->where('academic_id', getAcademicId());
                })
                ->get();

            $students = StudentRecord::where('school_id', Auth::user()->school_id)
                ->where('un_academic_id', getAcademicId())
                ->whereHas('student', function ($q): void {
                    $q->where('active_status', 1);
                })
                ->get();

            $fees_dues = [];
            $fees_due_ids = [];

            $fees_assigns = UnFeesInstallmentAssign::get();
            foreach ($fees_assigns as $fee_assign) {
                $discount_amount = $fee_assign->discount_amount;
                $total_amount = $fee_assign->amount;
                $amount = $fee_assign->paid_amount;
                $paid = $discount_amount + $amount;

                if ($total_amount > $paid) {
                    $due_date = strtotime($fee_assign->due_date);
                    $now = strtotime(date('Y-m-d'));
                    if ($due_date > $now) {
                        continue;
                    }

                    $fees_due_ids[] = $fee_assign->id;
                }
            }

            $fees_dues = $fees_assigns->whereIn('id', $fees_due_ids);

            return view('backEnd.feesCollection.search_fees_due', ['fees_masters' => $fees_masters, 'fees_dues' => $fees_dues]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function searchFeesDue(Request $request)
    {
        if (moduleStatusCheck('University')) {
            return $this->universitySearchFeesDue($request);
        }

        /*
                try {
                */
        $classes = SmClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id',Auth::user()->school_id)
            ->get();

        $fees_masters = SmFeesMaster::select('fees_group_id')
            ->where('active_status', 1)
            ->distinct('fees_group_id')
            ->where('school_id',Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        $students = StudentRecord::where('school_id',Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->whereHas('student',function($q){
                $q->where('active_status',1);
            })
            ->get();

        $fees_dues = [];
        $fees_due_ids = [];

        $fees_assigns = SmFeesAssign::with('feesGroupMaster', 'recordDetail', 'feesGroupMaster.feesTypes')->get();
        foreach ($fees_assigns as $assignFees) {
            $discount_amount = $assignFees->applied_discount;
            $total_amount = $assignFees->feesGroupMaster->amount;
            $amount = $assignFees->totalPaid;
            $paid = $discount_amount + $amount;

            if ($total_amount > $paid) {
                $due_date= strtotime($assignFees->feesGroupMaster->date);
                $now =strtotime(date('Y-m-d'));
                if ($due_date > $now ) {
                    continue;
                }
                $fees_due_ids[] = $assignFees->id;
            }
        }
        $fees_dues = $fees_assigns->whereIn('id', $fees_due_ids);
        return view('backEnd.feesCollection.search_fees_due', compact('classes', 'fees_masters', 'fees_dues'));
        /*
    } catch (\Exception $e) {
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }
    */

    }

    public function feesDueSearch(Request $request)
    {
        $input = $request->all();
        if (moduleStatusCheck('University')) {
            $validator = Validator::make($input, [
                'un_session_id' => 'required',
                'un_semester_label_id' => 'required',
            ]);
        } elseif (directFees()) {
            $validator = Validator::make($input, [
                'date_range' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'fees_group' => 'required',
                'class' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
            if (moduleStatusCheck('University')) {
                $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';
                $date_from = new DateTimeImmutable(trim(date('m/d/Y')));
                $date_to = new DateTimeImmutable(trim(date('m/d/Y')));

                $fees_masters = SmFeesMaster::select('fees_group_id')
                    ->where('active_status', 1)
                    ->distinct('fees_group_id')
                    ->where('school_id', Auth::user()->school_id)
                    ->when(moduleStatusCheck('University'), function ($q): void {
                        $q->where('un_academic_id', getAcademicId());
                    })
                    ->when(! moduleStatusCheck('University'), function ($q): void {
                        $q->where('academic_id', getAcademicId());
                    })
                    ->get();

                $fees_dues = UnFeesInstallmentAssign::whereHas('recordDetail')->whereIn('active_status', [0, 2])
                    ->where('un_semester_label_id', $request->un_semester_label_id)
                    ->when($request->date_range, function ($q) use ($date_from, $date_to): void {
                        $q->where('due_date', '>=', $date_from);
                        $q->where('due_date', '<=', $date_to);
                    })
                    ->get();

                return view('backEnd.feesCollection.search_fees_due', ['fees_dues' => $fees_dues, 'fees_masters' => $fees_masters]);
            }

            if (directFees()) {
                $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';

                $date_from = new DateTimeImmutable(trim($rangeArr[0]));
                $date_to = new DateTimeImmutable(trim($rangeArr[1]));

                $classes = SmClass::get();

                $allStudent = StudentRecord::when($request->class, function ($q) use ($request): void {
                    $q->where('class_id', $request->class);
                })
                    ->when($request->section, function ($q) use ($request): void {
                        $q->where('section_id', $request->section);
                    })
                    ->where('academic_id', getAcademicId())
                    ->get();

                $fees_dues = DirectFeesInstallmentAssign::whereHas('recordDetail')->whereIn('active_status', [0, 2])
                    ->whereIn('record_id', $allStudent->pluck('id'))
                    ->where('school_id', auth()->user()->school_id)
                    ->when($request->date_range, function ($q) use ($date_from, $date_to): void {
                        $q->where('due_date', '>=', $date_from);
                        $q->where('due_date', '<=', $date_to);
                    })
                    ->get();

                return view('backEnd.feesCollection.search_fees_due', ['fees_dues' => $fees_dues, 'date_to' => $date_to, 'date_from' => $date_from, 'classes' => $classes]);
            }

            $fees_group = explode('-', $request->fees_group);
            $fees_master = SmFeesMaster::select('id', 'amount')
                ->where('fees_group_id', $fees_group[0])
                ->where('fees_type_id', $fees_group[1])
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $studentRecord = StudentRecord::where('class_id', $request->class)
                ->when($request->section, function ($q) use ($request): void {
                    $q->where('section_id', $request->section);
                })->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $fees_dues = [];
            foreach ($studentRecord as $record) {
                $fees_master = SmFeesMaster::select('id', 'amount', 'date')
                    ->where('fees_group_id', $fees_group[0])
                    ->where('fees_type_id', $fees_group[1])
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $total_amount = $fees_master->amount;

                $fees_assign = SmFeesAssign::where('student_id', $record->student_id)
                    ->where('record_id', $record->id)
                    ->where('fees_master_id', $fees_master->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->first();

                $discount_amount = SmFeesAssign::where('student_id', $record->student_id)
                    ->where('record_id', $record->id)
                    ->where('academic_id', getAcademicId())
                    ->where('fees_master_id', $fees_master->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('applied_discount');

                $amount = SmFeesPayment::where('active_status', 1)
                    ->where('student_id', $record->student_id)
                    ->where('record_id', $record->id)
                    ->where('academic_id', getAcademicId())
                    ->where('fees_type_id', $fees_group[1])
                    ->sum('amount');

                $paid = $discount_amount + $amount;
                if ($fees_assign !== '' && $total_amount > $paid) {
                    $fees_dues[] = $fees_assign;
                }
            }

            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $fees_masters = SmFeesMaster::select('fees_group_id')
                ->where('active_status', 1)
                ->distinct('fees_group_id')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $class_id = $request->class;
            $fees_group_id = $fees_group[1];

            return view('backEnd.feesCollection.search_fees_due', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_dues' => $fees_dues, 'class_id' => $class_id, 'fees_group_id' => $fees_group_id]);

/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function sendDuesFeesEmail(Request $request){
        /*
        try{
        */
            if(empty($request->student_list)) {
                Toastr::warning('Student Not Found', 'Warning');

                return redirect()->back();
            }

            $students = SmStudent::with('parents')->whereIn('id', $request->student_list)->get();

            $fees_info = SmFeesMaster::with('feesType', 'feesGroups')->find($request->fees_master);

            foreach ($students as $student) {

                $studentId = $student->id;
                if (isset($request->dues_amount[$studentId])) {
                    $compact['due_amount'] = $request->dues_amount[$studentId];
                } else {
                    $compact['due_amount'] = 0;
                }

                // $compact['due_amount'] = $request->dues_amount[$student->id];
                $compact['fees_name'] = @$fees_info->feesType->name;
                $compact['student_name'] = @$student->full_name;
                $compact['date'] = @$fees_info->feesGroups->due_date ?? date('Y-m-d');

                $compact['student_detail'] = $student;

                if (property_exists($request, 'send_sms') && $request->send_sms !== null) {
                    @send_sms($student->mobile, 'student_fees_due', $compact);
                    $compact['user_email'] = @$student->parents->guardians_email;
                    $compact['parent_name'] = @$student->parents->guardians_name;
                    @send_sms(@$student->parents->guardians_mobile, 'student_dues_fees_for_parent', $compact);
                } elseif (property_exists($request, 'send_email') && $request->send_email !== null) {
                    if ($student->email) {
                        send_mail($student->email, $student->full_name, 'due_fees_payment', $compact);
                    }

                    if (@$student->parents->guardians_email) {
                        send_mail(@$student->parents->guardians_email, $student->parents->guardians_name, 'due_fees_payment', $compact);
                    }
                }

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

    public function feesStatemnt(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::get();

            return view('backEnd.feesCollection.fees_statment', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesStatementSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'student' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
            $classes = SmClass::get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->distinct('fees_group_id')->get();
            $student = SmStudent::find($request->student);
            $fees_assigneds = SmFeesAssign::where('student_id', $request->student)->where('school_id', Auth::user()->school_id)->get();
            if ($fees_assigneds->count() <= 0) {
                Toastr::error('Fees assigned not yet!');

                return redirect()->back();
            }

            $fees_discounts = SmFeesAssignDiscount::where('student_id', $request->student)->where('school_id', Auth::user()->school_id)->get();
            $applied_discount = [];
            foreach ($fees_discounts as $fee_discount) {
                $fees_payment = SmFeesPayment::where('active_status', 1)->select('fees_discount_id')->where('fees_discount_id', $fee_discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $class_id = $request->class;

            return view('backEnd.feesCollection.fees_statment', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_assigneds' => $fees_assigneds, 'fees_discounts' => $fees_discounts, 'applied_discount' => $applied_discount, 'student' => $student, 'class_id' => $class_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesInvoice($sid, $pid, $faid)
    {
        /*
        try {
        */
            return view('backEnd.feesCollection.fees_collect_invoice');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesGroupPrint($id): void
    {
        $fees_assigned = SmFeesAssign::find($id);
        SmStudent::find($fees_assigned->student_id);
    }

    public function feesPaymentPrint($id, $group)
    {
        /*
        try {
        */
            // $payment = SmFeesPayment::find($id);
            if (checkAdmin() == true) {
                $payment = SmFeesPayment::find($id);
            } else {
                $payment = SmFeesPayment::where('active_status', 1)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $student = SmStudent::find($payment->student_id);
            $pdf = Pdf::loadView('backEnd.feesCollection.fees_payment_print', ['payment' => $payment, 'group' => $group, 'student' => $student]);

            return $pdf->stream(date('d-m-Y').'-'.$student->full_name.'-fees-payment-details.pdf');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesPaymentInvoicePrint($id, $s_id)
    {

        /*
        try {
        */
            set_time_limit(2700);
            $groups = explode('-', $id);
            $student = StudentRecord::find($s_id);
            if (moduleStatusCheck('University')) {
                foreach ($groups as $group) {
                    $fees_assigneds[] = UnFeesInstallmentAssign::find($group);
                }
            } elseif (directFees()) {
                foreach ($groups as $group) {
                    $fees_assigneds[] = DirectFeesInstallmentAssign::find($group);
                }
            } else {
                foreach ($groups as $group) {
                    $fees_assigneds[] = SmFeesAssign::find($group);
                }
            }

            $parent = SmParent::where('id', $student->studentDetail->parent_id)
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $unapplied_discount_amount = SmFeesAssignDiscount::where('student_id', $student->student_id)
                ->where('record_id', $student->id)
                ->where('school_id', Auth::user()->school_id)
                ->sum('unapplied_amount');
            $invoiceSettings = InvoiceSetting::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();

            return view('backEnd.feesCollection.fees_payment_invoice_print')->with(['invoiceSettings' => $invoiceSettings, 'fees_assigneds' => $fees_assigneds, 'student' => $student, 'unapplied_discount_amount' => $unapplied_discount_amount, 'parent' => $parent, 'id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function feesGroupsPrint($id, $s_id)
    {
        /*
        try {
        */
            $groups = explode('-', $id);
            $student = SmStudent::find($s_id);
            foreach ($groups as $group) {
                $fees_assigneds[] = SmFeesAssign::find($group);
            }

            $pdf = Pdf::loadView('backEnd.feesCollection.fees_groups_print', ['fees_assigneds' => $fees_assigneds, 'student' => $student]);

            return $pdf->stream(date('d-m-Y').'-'.$student->full_name.'-fees-groups-details.pdf');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentFineReport(Request $request)
    {
        /*
        try {
        */
            return view('backEnd.reports.student_fine_report');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentFineReportSearch(Request $request)
    {
        /*
        try {
        */
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)->where('payment_date', '<=', $date_to)->where('fine', '!=', 0)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.reports.student_fine_report', ['fees_payments' => $fees_payments]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    //

    public function fineReport()
    {
        $classes = SmClass::get();

        return view('backEnd.accounts.fine_report', ['classes' => $classes]);
    }

    public function universityFineReportSearch($request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';
        /*
        try {
        */

            if ($request->date_range) {
                $date_from = new DateTimeImmutable(trim($rangeArr[0]));
                $date_to = new DateTimeImmutable(trim($rangeArr[1]));
            }

            if ($request->date_range) {
                $fine_info = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $fine_info = $fine_info->groupBy('student_id');
            }

            $students = StudentRecord::with('student')
                ->when($request->un_semester_label_id, function ($q) use ($request): void {
                    $q->where('un_semester_label_id', $request->un_semester_label_id);
                })
                ->when($request->section_id, function ($q) use ($request): void {
                    $q->where('section_id', $request->section_id);
                })->pluck('student_id')->toArray();

            $fine_info = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                ->where('payment_date', '<=', $date_to)
                ->where('school_id', Auth::user()->school_id)
                ->whereIn('student_id', $students)
                ->get();
            $fine_info = $fine_info->distinct('student_id');

            return view('backEnd.accounts.fine_report', ['fine_info' => $fine_info]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function fineReportSearch(SmFineReportSearchRequest $request)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityFineReportSearch($request);
        }

        $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';

            /*
            try {
            */
                $classes = SmClass::get();

                if($request->date_range){
                    $date_from = new \DateTime(trim($rangeArr[0]));
                    $date_to =  new \DateTime(trim($rangeArr[1]));
                }

                if($request->date_range ){
                    $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id',Auth::user()->school_id)
                        ->get();

                    $fine_info = $fine_info->groupBy('student_id');
                }

                if($request->class){
                    $students=SmStudent::where('class_id',$request->class)
                        ->get();

                    $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id',Auth::user()->school_id)
                        ->whereIn('student_id', $students)
                        ->get();
                    $fine_info = $fine_info->groupBy('student_id');

                }

                if($request->class && $request->section){

                    $students=SmStudent::where('class_id',$request->class)
                        ->where('section_id',$request->section)
                        ->get();

                    $fine_info = SmFeesPayment::where('active_status',1)->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id',Auth::user()->school_id)
                        ->whereIn('student_id', $students)
                        ->get();

                    $fine_info = $fine_info->distinct('student_id');
                }
                return view('backEnd.accounts.fine_report',compact('classes','fine_info'));
            /*
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
            */


    }

    public function directFeesGenerateModal(Request $request, $amount, $installment_id, $record_id)
    {
        /*
        try {
        */
            $amount = $amount;
            $studentRerod = StudentRecord::find($record_id);
            $student_id = $studentRerod->student_id;

            $banks = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();
            $discounts = [];
            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();
            $installment = DirectFeesInstallmentAssign::find($installment_id);
            $balace_amount = discountFees($installment->id) - $installment->paid_amount;

            return view('backEnd.feesCollection.directFees.fees_generate_modal', ['amount' => $amount, 'discounts' => $discounts, 'installment_id' => $installment_id, 'student_id' => $student_id, 'data' => $data, 'method' => $method, 'banks' => $banks, 'record_id' => $record_id, 'balace_amount' => $balace_amount]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function directFeesInstallmentUpdate(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'due_date' => 'required',
        ]);

        $installment = DirectFeesInstallmentAssign::find($request->installment_id);
        $installment->amount = $request->amount;
        $installment->due_date = date('Y-m-d', strtotime($request->due_date));
        if ($installment->fees_discount_id) {
            $fees_discount = SmFeesDiscount::find($installment->fees_discount_id);
            $installment->discount_amount = ($installment->amount * $fees_discount->amount) / 100;
        }

        $installment->save();
        Toastr::success('Operation Successfull', 'Success');

        return redirect()->back();
    }

    public function editSubPaymentModal($payment_id, $amount)
    {
        $payment = DireFeesInstallmentChildPayment::find($payment_id);

        return view('backEnd.feesCollection.directFees.editSubPaymentModal', ['amount' => $amount, 'payment' => $payment]);
    }

    public function updateSubPaymentModal(Request $request)
    {
        $payment = DireFeesInstallmentChildPayment::find($request->sub_payment_id);
        $installment = DirectFeesInstallmentAssign::find($payment->direct_fees_installment_assign_id);
        $dif_amount = $request->amount - $payment->paid_amount;
        if ($payment) {
            $payment->paid_amount = $request->amount;
            $payment->amount = $request->amount;
            $payment->balance_amount -= $dif_amount;
            $payment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $payment->save();
            $sm_fees_payment = SmFeesPayment::where('installment_payment_id', $payment->id)->first();
            if ($sm_fees_payment) {
                $sm_fees_payment->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $sm_fees_payment->amount = $request->amount;
                $sm_fees_payment->save();
            }

            $installment->paid_amount += $dif_amount;
            if ($installment->paid_amount == discountFees($installment->id)) {
                $installment->active_status = 1;
            } elseif ($installment->paid_amount !== discountFees($installment->id)) {
                $installment->active_status = 2;
            }

            $installment->save();
        }

        Toastr::success('Operation Successfull', 'Success');

        return redirect()->back();
    }

    public function deleteSubPayment(Request $request)
    {
        $payment = DireFeesInstallmentChildPayment::find($request->sub_payment_id);
        if ($payment) {
            $installment = DirectFeesInstallmentAssign::find($payment->direct_fees_installment_assign_id);
            $installment->paid_amount -= $payment->paid_amount;
            if (($installment->paid_amount == 0)) {
                $installment->active_status = 0;
            } elseif ($installment->paid_amount == discountFees($installment->id)) {
                $installment->active_status = 1;
            } else {
                $installment->active_status = 2;
            }

            $installment->save();
            $fees_payment = SmFeesPayment::where('installment_payment_id', $payment->id)->first();
            if ($fees_payment) {
                $income = SmAddIncome::where('fees_collection_id', $fees_payment->id)->first();
                $statement = SmBankStatement::where('fees_payment_id', $fees_payment->id)->first();
                if ($statement) {
                    $bank = SmBankAccount::where('id', $statement->bank_id)
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    $after_balance = $bank->current_balance - $statement->amount;
                    $bank->current_balance = $after_balance;
                    $bank->update();
                    $statement->delete();
                }

                if ($income) {
                    $income->delete();
                }

                $fees_payment->delete();
            }

            $payment->delete();
        }

        Toastr::success('Operation Successfull', 'Success');

        return redirect()->back();
    }

    public function viewPaymentReceipt($id)
    {

        $feesInstallment = DireFeesInstallmentChildPayment::find($id);
        $oldPayments = DireFeesInstallmentChildPayment::where('id', '<', $id)->where('direct_fees_installment_assign_id', $feesInstallment->direct_fees_installment_assign_id)->where('active_status', 1)->sum('paid_amount');
        $student = StudentRecord::find($feesInstallment->record_id);

        return view('backEnd.feesCollection.directFees.viewPaymentReceipt', ['feesInstallment' => $feesInstallment, 'student' => $student, 'id' => $id, 'oldPayments' => $oldPayments]);
    }

    public function directFeesSetting()
    {

        $data['model'] = DirectFeesSetting::where('school_id', auth()->user()->school_id)

            ->first();

        $data['feesInvoice'] = FeesInvoice::where('school_id', auth()->user()->school_id)
            ->first();

        $data['paymentReminder'] = DirectFeesReminder::where('school_id', auth()->user()->school_id)

            ->first();

        if (is_null($data['model'])) {
            $directFeesSetting = new DirectFeesSetting();
            $directFeesSetting->school_id = Auth::user()->school_id;
            $directFeesSetting->save();
            $data['model'] = $directFeesSetting;
        }

        if (is_null($data['feesInvoice'])) {
            $feesInvoice = new FeesInvoice();
            $feesInvoice->school_id = Auth::user()->school_id;
            $feesInvoice->prefix = 'ABC_';
            $feesInvoice->start_form = 1;
            $feesInvoice->save();
            $data['feesInvoice'] = $feesInvoice;
        }

        if (is_null($data['paymentReminder'])) {
            $directFeesReminder = new DirectFeesReminder();
            $directFeesReminder->school_id = Auth::user()->school_id;
            $directFeesReminder->due_date_before = 5;
            $directFeesReminder->notification_types = '';
            $directFeesReminder->save();
            $data['paymentReminder'] = $directFeesReminder;
        }

        return view('backEnd.feesCollection.directFees.directFeesSetting')->with($data);
    }

    public function feesInvoiceUpdate(Request $request)
    {

        /*
        try {
        */
            $setting = FeesInvoice::where('school_id', $request->school_id)->first();
            $new = $setting ?: new FeesInvoice();
            $new->prefix = $request->prefix;
            $new->start_form = $request->start_form;
            $new->school_id = $request->school_id;
            $new->save();

            Toastr::success('Operation Successfull', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function paymentReminder(Request $request)
    {

        /*
        try {
        */
            $setting = DirectFeesReminder::where('school_id', $request->school_id)->first();
            $new = $setting ?: new DirectFeesReminder();
            $new->due_date_before = $request->due_date_before;
            $new->notification_types = json_encode($request->notification_types);
            $new->school_id = $request->school_id;
            $new->save();

            Toastr::success('Operation Successfull', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function directFeesTotalPayment($record_id)
    {

        /*
        try {
        */
            $studentRerod = StudentRecord::find($record_id);
            $student_id = $studentRerod->student_id;

            $banks = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();
            $discounts = [];
            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $total_amount = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('amount');
            $total_discount = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('discount_amount');
            $total_paid = DirectFeesInstallmentAssign::where('record_id', $record_id)->sum('paid_amount');
            $balace_amount = $total_amount - ($total_discount + $total_paid);
            $amount = $balace_amount;

        /*
        }
        catch(\Exception $e){
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function directFeesTotalPaymentSubmit(Request $request)
    {
        /*
        try {
        */
            $this->addPayment($request);
            Toastr::success('Operation Successfull', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function addPayment($request)
    {
        $record_id = $request->record_id;
        $request_amount = $request->request_amount;
        $after_paid = $request_amount;

        $installments = DirectFeesInstallmentAssign::where('record_id', $record_id)->get();
        $total_paid = $installments->sum('paid_amount');
        $total_amount = $installments->sum('amount');
        $total_discount = $installments->sum('discount_amount');
        $balace_amount = $total_amount - ($total_discount + $total_paid);
        if ($balace_amount < $request_amount) {
            Toastr::error('Amount is greater than due', 'Failed');

            return redirect()->back();
        }

        foreach ($installments as $installment) {
            if ($after_paid <= 0) {
                break;
            }

            $installment_due = $installment->amount - ($installment->discount_amount + $installment->paid_amount);
            if ($installment_due && $after_paid > 0) {
                $paid_amount = $installment_due >= $after_paid ? $after_paid : $installment_due;
                // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                $fileName = '';
                if ($request->file('slip') && $request->file('slip') !== '') {
                    $file = $request->file('slip');
                    $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                    $file->move('public/uploads/bankSlip/', $fileName);
                    $fileName = 'public/uploads/bankSlip/'.$fileName;
                }

                $discount_group = explode('-', $request->discount_group);
                $user = Auth::user();
                $fees_payment = new SmFeesPayment();
                $fees_payment->student_id = $request->student_id;
                $fees_payment->fees_discount_id = empty($request->fees_discount_id) ? null : $request->fees_discount_id;
                $fees_payment->discount_amount = empty($request->applied_amount) ? 0 : $request->applied_amount;
                $fees_payment->fine = empty($request->fine) ? 0 : $request->fine;
                $fees_payment->assign_id = $request->assign_id;
                $fees_payment->amount = $paid_amount;
                $fees_payment->assign_id = $request->assign_id;
                $fees_payment->payment_date = date('Y-m-d', strtotime($request->date));
                $fees_payment->payment_mode = $request->payment_mode;
                $fees_payment->created_by = $user->id;
                $fees_payment->note = $request->note;
                $fees_payment->fine_title = $request->fine_title;
                $fees_payment->school_id = Auth::user()->school_id;
                $fees_payment->slip = $fileName;
                $fees_payment->record_id = $request->record_id;
                $fees_payment->academic_id = getAcademicid();
                if (moduleStatusCheck('University')) {
                    $fees_payment->un_academic_id = getAcademicId();
                    $fees_payment->un_fees_installment_id = $request->installment_id;
                    $fees_payment->un_semester_label_id = $request->un_semester_label_id;

                    $payable_amount = discountFeesAmount($installment->id);
                    $sub_payment = $installment->payments->sum('paid_amount');
                    $direct_payment = $installment->paid_amount;
                    $total_paid = $sub_payment + $direct_payment;
                    $installment->payment_date = date('Y-m-d', strtotime($request->date));

                    $last_inovoice = UnFeesInstallAssignChildPayment::where('school_id', auth()->user()->school_id)->max('invoice_no');
                    $new_subPayment = new UnFeesInstallAssignChildPayment();
                    $new_subPayment->un_fees_installment_assign_id = $installment->id;
                    $new_subPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                    $new_subPayment->amount = $paid_amount;
                    $new_subPayment->paid_amount = $paid_amount;
                    $new_subPayment->payment_date = $fees_payment->payment_date;
                    $new_subPayment->payment_mode = $fees_payment->payment_mode;
                    $new_subPayment->note = $request->note;
                    $new_subPayment->slip = $fileName;
                    $new_subPayment->active_status = 1;
                    $new_subPayment->bank_id = $request->bank_id;
                    $new_subPayment->discount_amount = 0;
                    $new_subPayment->fees_type_id = $installment->fees_type_id;
                    $new_subPayment->student_id = $request->student_id;
                    $new_subPayment->record_id = $request->record_id;
                    $new_subPayment->un_semester_label_id = $request->un_semester_label_id;
                    $new_subPayment->un_academic_id = getAcademicId();
                    $new_subPayment->created_by = Auth::user()->id;
                    $new_subPayment->updated_by = Auth::user()->id;
                    $new_subPayment->school_id = Auth::user()->school_id;
                    $new_subPayment->balance_amount = ($payable_amount - ($sub_payment + $paid_amount));
                    $new_subPayment->save();
                    $fees_payment->installment_payment_id = $new_subPayment->id;
                    $installment->active_status = ($sub_payment + $paid_amount) == $payable_amount ? 1 : 2;
                    $installment->paid_amount = $sub_payment + $paid_amount;
                    $installment->save();

                } elseif (directFees()) {
                    $payable_amount = discountFees($installment->id);
                    $sub_payment = $installment->payments->sum('paid_amount');
                    $direct_payment = $installment->paid_amount;
                    $total_paid = $sub_payment + $direct_payment;
                    $fees_payment->direct_fees_installment_assign_id = $installment->id;
                    $fees_payment->academic_id = getAcademicId();

                    $last_inovoice = DireFeesInstallmentChildPayment::where('school_id', auth()->user()->school_id)->max('invoice_no');
                    $new_subPayment = new DireFeesInstallmentChildPayment();
                    $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                    $new_subPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                    $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                    $new_subPayment->amount = $paid_amount;
                    $new_subPayment->paid_amount = $paid_amount;
                    $new_subPayment->payment_date = $fees_payment->payment_date;
                    $new_subPayment->payment_mode = $fees_payment->payment_mode;
                    $new_subPayment->note = $request->note;
                    $new_subPayment->slip = $fileName;
                    $new_subPayment->active_status = 1;
                    $new_subPayment->bank_id = $request->bank_id;
                    $new_subPayment->discount_amount = 0;
                    $new_subPayment->fees_type_id = $installment->fees_type_id;
                    $new_subPayment->student_id = $request->student_id;
                    $new_subPayment->record_id = $request->record_id;

                    $new_subPayment->created_by = Auth::user()->id;
                    $new_subPayment->updated_by = Auth::user()->id;
                    $new_subPayment->school_id = Auth::user()->school_id;
                    $new_subPayment->balance_amount = ($payable_amount - ($sub_payment + $paid_amount));
                    $new_subPayment->save();
                    $fees_payment->installment_payment_id = $new_subPayment->id;

                    $installment->active_status = ($sub_payment + $paid_amount) == $payable_amount ? 1 : 2;
                    $installment->paid_amount = $sub_payment + $paid_amount;
                    $installment->save();
                } else {
                    $fees_payment->fees_type_id = $request->fees_type_id;
                    $fees_payment->academic_id = getAcademicId();
                }

                $result = $fees_payment->save();
                $payment_mode_name = ucwords($request->payment_mode);
                $payment_method = SmPaymentMethhod::where('method', $payment_mode_name)->first();
                $income_head = generalSetting();

                $add_income = new SmAddIncome();
                $add_income->name = 'Fees Collect';
                $add_income->date = date('Y-m-d', strtotime($request->date));
                $add_income->amount = $fees_payment->amount;
                $add_income->fees_collection_id = $fees_payment->id;
                $add_income->active_status = 1;
                $add_income->income_head_id = $income_head->income_head_id;
                $add_income->payment_method_id = $payment_method->id;
                $add_income->account_id = $request->bank_id;
                $add_income->created_by = Auth()->user()->id;
                $add_income->school_id = Auth::user()->school_id;
                if (moduleStatusCheck('University')) {
                    $add_income->un_academic_id = getAcademicId();
                }

                $add_income->academic_id = getAcademicId();
                $add_income->save();

                if ($payment_method->id == 3) {
                    $bank = SmBankAccount::where('id', $request->bank_id)
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    $after_balance = $bank->current_balance + $paid_amount;

                    $bank_statement = new SmBankStatement();
                    $bank_statement->amount = $paid_amount;
                    $bank_statement->after_balance = $after_balance;
                    $bank_statement->type = 1;
                    $bank_statement->details = 'Fees Payment';
                    $bank_statement->payment_date = date('Y-m-d', strtotime($request->date));
                    $bank_statement->bank_id = $request->bank_id;
                    $bank_statement->school_id = Auth::user()->school_id;
                    $bank_statement->payment_method = $payment_method->id;
                    $bank_statement->fees_payment_id = $fees_payment->id;
                    $bank_statement->save();

                    $current_balance = SmBankAccount::find($request->bank_id);
                    $current_balance->current_balance = $after_balance;
                    $current_balance->update();
                }

                $after_paid -= ($paid_amount);
            }
        }

        return null;
    }
}
