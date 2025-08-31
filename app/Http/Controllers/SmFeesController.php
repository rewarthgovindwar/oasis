<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\Models\StudentRecord;
use App\Notifications\FeesApprovedNotification;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankPaymentSlip;
use App\SmBankStatement;
use App\SmClass;
use App\SmFeesAssign;
use App\SmFeesAssignDiscount;
use App\SmFeesCarryForward;
use App\SmFeesMaster;
use App\SmFeesPayment;
use App\SmGeneralSettings;
use App\SmNotification;
use App\SmParent;
use App\SmPaymentGatewaySetting;
use App\SmPaymentMethhod;
use App\SmSection;
use App\SmSmsGateway;
use App\SmsTemplate;
use App\SmStudent;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Twilio\Rest\Client;

class SmFeesController extends Controller
{

    public function feesForward(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }

            return view('backEnd.feesCollection.fees_forward', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesForwardSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();
            if ($students->count() !== 0) {
                foreach ($students as $student) {
                    $fees_balance = SmFeesCarryForward::where('student_id', $student->id)->count();
                }

                $class_id = $request->class;

                if ($fees_balance == 0) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        $data = [];
                        $data['classes'] = $classes->toArray();
                        $data['students'] = $students->toArray();
                        $data['class_id'] = $class_id;

                        return ApiBaseMethod::sendResponse($data, null);
                    }

                    return view('backEnd.feesCollection.fees_forward', ['classes' => $classes, 'students' => $students, 'class_id' => $class_id]);
                }

                $update = '';

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    $data = [];
                    $data['classes'] = $classes->toArray();
                    $data['students'] = $students->toArray();
                    $data['class_id'] = $class_id;
                    $data['update'] = $update;

                    return ApiBaseMethod::sendResponse($data, null);
                }

                return view('backEnd.feesCollection.fees_forward', ['classes' => $classes, 'students' => $students, 'update' => $update, 'class_id' => $class_id]);

            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('No result Found');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect('fees-forward');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesForwardStore(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->id as $student) {

                if ($request->update == 1) {

                    $fees_forward = SmFeesCarryForward::find($student);
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->notes = $request->notes[$student];
                    $fees_forward->save();
                } else {
                    $fees_forward = new SmFeesCarryForward();
                    $fees_forward->student_id = $student;
                    $fees_forward->balance = $request->balance[$student];
                    $fees_forward->notes = $request->notes[$student];
                    $fees_forward->school_id = Auth::user()->school_id;
                    $fees_forward->academic_id = getAcademicId();
                    $fees_forward->save();
                }
            }

            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Fees has been forwarded successfully');
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('fees-forward');
        } catch (Exception $exception) {
            DB::rollback();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function collectFees(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($classes, null);
            }

            return view('backEnd.feesCollection.collect_fees', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function collectFeesSearch(Request $request)
    {
        $request->all();
        // $validator = Validator::make($input, [
        //     'class' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }
        try {
            $students = SmStudent::query();

            if ($request->class !== '') {
                $students->where('class_id', $request->class);
            }

            if ($request->section !== '') {
                $students->where('section_id', $request->section);
            }

            if ($request->keyword !== '') {
                $students->where('full_name', 'like', '%'.$request->keyword.'%')->orWhere('admission_no', $request->keyword)->orWhere('roll_no', $request->keyword)->orWhere('national_id_no', $request->keyword)->orWhere('local_id_no', $request->keyword);
            }

            $students = $students->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('active_status', 1)->get();

            if ($students->isEmpty()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('No result found');
                }

                Toastr::error('No result found', 'Failed');

                return redirect('collect-fees');
            }

            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['students'] = $students->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            $class_info = SmClass::find($request->class);
            $search_info['class_name'] = @$class_info->class_name;
            if ($request->section !== '') {
                $section_info = SmSection::find($request->section);
                $search_info['section_name'] = @$section_info->section_name;
            }

            if ($request->keyword !== '') {
                $search_info['keyword'] = $request->keyword;
            }

            return view('backEnd.feesCollection.collect_fees', ['classes' => $classes, 'students' => $students, 'search_info' => $search_info]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function collectFeesStudent(Request $request, $id)
    {
        try {
            // $student = SmStudent::find($id);
            if (checkAdmin() == true) {
                $student = SmStudent::find($id);
            } else {
                $student = SmStudent::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $fees_assigneds = SmFeesAssign::where('student_id', $id)
                ->orderBy('id', 'desc')
                ->where('school_id', Auth::user()->school_id)
                ->get();
            if (count($fees_assigneds) <= 0) {
                Toastr::warning('Fees assign not yet!');

                return redirect('/collect-fees');
            }

            $fees_assigneds2 = DB::table('sm_fees_assigns')
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                ->select('sm_fees_types.id as fees_type_id', 'sm_fees_assigns.fees_amount', 'sm_fees_assigns.applied_discount', 'sm_fees_assigns.id', 'sm_fees_assigns.student_id', 'sm_fees_types.name', 'sm_fees_masters.date as due_date', 'sm_fees_masters.amount', 'sm_fees_masters.fees_group_id', 'sm_fees_masters.id as fees_master_id', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_assigns.student_id', $id)
                ->where('sm_fees_assigns.school_id', Auth::user()->school_id)->get();
            // return $fees_assigneds2;
            $i = 0;
            foreach ($fees_assigneds2 as $fee_assigneds2) {
                $d[$i]['fees_type_id'] = $fee_assigneds2->fees_type_id;
                $d[$i]['fees_name'] = $fee_assigneds2->name;
                $d[$i]['due_date'] = $fee_assigneds2->due_date;
                $d[$i]['amount'] = $fee_assigneds2->fees_amount;
                $d[$i]['applied_discount'] = $fee_assigneds2->applied_discount;
                // $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $fee_assigneds2->student_id)->sum('amount');
                $d[$i]['fine'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $fee_assigneds2->student_id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $fee_assigneds2->student_id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine']) - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }

            $fees_discounts = SmFeesAssignDiscount::where('student_id', $id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fee_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status', 1)->where('fees_discount_id', $fee_discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees'] = $d;

                return ApiBaseMethod::sendResponse($data, null);
            }

            $fees_assigneds = SmFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.feesCollection.collect_fees_student_wise', ['student' => $student, 'fees_assigneds' => $fees_assigneds, 'fees_discounts' => $fees_discounts, 'applied_discount' => $applied_discount]);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function collectFeesStudentApi(Request $request, $id)
    {
        try {
            $student = SmStudent::where('user_id', $id)->where('school_id', Auth::user()->school_id)->first();
            $fees_assigneds = SmFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->get();

            $fees_assigneds2 = DB::table('sm_fees_assigns')
                ->select('sm_fees_types.id as fees_type_id', 'sm_fees_types.name', 'sm_fees_masters.date as due_date', 'sm_fees_masters.amount as amount')
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                // ->join('sm_fees_payments', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_assigns.student_id', $student->id)
                ->where('sm_fees_assigns.school_id', Auth::user()->school_id)->get();

            // return $fees_assigneds2;
            $i = 0;
            $d = [];
            foreach ($fees_assigneds2 as $fee_assigneds2) {
                $d[$i]['fees_type_id'] = $fee_assigneds2->fees_type_id;
                $d[$i]['fees_name'] = $fee_assigneds2->name;
                $d[$i]['due_date'] = $fee_assigneds2->due_date;
                $d[$i]['amount'] = $fee_assigneds2->amount;
                $d[$i]['paid'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $student->id)->sum('amount');
                $d[$i]['fine'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $student->id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('sm_fees_payments')->where('fees_type_id', $fee_assigneds2->fees_type_id)->where('student_id', $student->id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine']) - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }

            // , DB::raw("SUM(sm_fees_payments.amount) as total_paid where sm_fees_payments.fees_type_id==")
            $fees_discounts = SmFeesAssignDiscount::where('student_id', $id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fee_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status', 1)->where('fees_discount_id', $fee_discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $currency_symbol = SmGeneralSettings::select('currency_symbol')->where('school_id', Auth::user()->school_id)->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                // $data['student'] = $student;
                $data['fees'] = $d;
                $data['currency_symbol'] = $currency_symbol;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', ['student' => $student, 'fees_assigneds' => $fees_assigneds, 'fees_discounts' => $fees_discounts, 'applied_discount' => $applied_discount]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGenerateModal(Request $request, $amount, $student_id, $type, $master, $assign_id)
    {
        try {
            $fees_type_id = $type;

            $banks = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)
                ->where('fees_type_id', $fees_type_id)
                ->where('school_id', Auth::user()->school_id)
                ->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['amount'] = $amount;
                $data['discounts'] = $discounts;
                $data['fees_type_id'] = $fees_type_id;
                $data['student_id'] = $student_id;

                return ApiBaseMethod::sendResponse($data, null);
            }

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

            return view('backEnd.feesCollection.fees_generate_modal', ['amount' => $amount, 'assign_id' => $assign_id, 'master' => $master, 'discounts' => $discounts, 'fees_type_id' => $fees_type_id, 'student_id' => $student_id, 'data' => $data, 'method' => $method, 'banks' => $banks]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGenerateModalChild(Request $request, $amount, $student_id, $type)
    {
        try {
            $fees_type_id = $type;
            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($discounts as $discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('active_status', 1)->where('fees_discount_id', $discount->id)->where('school_id', Auth::user()->school_id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['amount'] = $amount;
                $data['discounts'] = $discounts;
                $data['fees_type_id'] = $fees_type_id;
                $data['student_id'] = $student_id;
                $data['applied_discount'] = $applied_discount;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_generate_modal_child', ['amount' => $amount, 'discounts' => $discounts, 'fees_type_id' => $fees_type_id, 'student_id' => $student_id, 'applied_discount' => $applied_discount]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesPaymentStore(Request $request)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
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
            $smFeesPayment->fees_type_id = $request->fees_type_id;
            $smFeesPayment->fees_discount_id = empty($request->fees_discount_id) ? '' : $request->fees_discount_id;
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
            $smFeesPayment->academic_id = getAcademicid();
            $result = $smFeesPayment->save();

            $payment_mode_name = ucwords($request->payment_mode);
            $payment_method = SmPaymentMethhod::where('method', $payment_mode_name)->first();
            $income_head = generalSetting();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Fees Collect';
            $smAddIncome->date = date('Y-m-d', strtotime($request->date));
            $smAddIncome->amount = empty($request->amount) ? 0 : $request->amount;
            $smAddIncome->fees_collection_id = $smFeesPayment->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $income_head->income_head_id;
            $smAddIncome->payment_method_id = $payment_method->id;
            if ($payment_method->id == 3) {
                $smAddIncome->account_id = $request->bank_id;
            }

            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
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

            // if ($request->discount_group) {
            //     $discount_assign=SmFeesAssignDiscount::where('fees_discount_id',$request->discount_group)->where('student_id',$request->student_id)->first();
            //     $discount_assign->applied_amount+=$request->discount_amount;
            //     $discount_assign->unapplied_amount-=$request->discount_amount;
            //     $discount_assign->save();
            // }

            $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)->where('student_id', $request->student_id)->where('school_id', Auth::user()->school_id)->first();
            $fees_assign->fees_amount -= (float) ($request->amount);
            $fees_assign->save();
            if (! empty($request->fine)) {
                $fees_assign = SmFeesAssign::where('fees_master_id', $request->master_id)->where('student_id', $request->student_id)->where('school_id', Auth::user()->school_id)->first();
                $fees_assign->fees_amount += $request->fine;
                $fees_assign->save();
            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return Redirect::route('fees_collect_student_wise', ['id' => $request->student_id]);
            }

            Toastr::error('Operation Failed', 'Failed');

            return Redirect::route('fees_collect_student_wise', ['id' => $request->student_id]);

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesPaymentDelete(Request $request)
    {
        try {
            $assignFee = SmFeesAssign::find($request->assign_id);

            if ($assignFee) {
                $newAmount = $assignFee->fees_amount + $request->amount;
                $assignFee->fees_amount = $newAmount;
                $assignFee->save();
            }

            if (checkAdmin() == true) {

                $result = SmFeesPayment::destroy($request->id);
            } else {

                $result = SmFeesPayment::where('active_status', 1)->where('id', $request->id)->where('school_id', Auth::user()->school_id)->delete();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees payment has been deleted  successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function searchFeesPayment(Request $request)
    {
        try {

            if (auth()->user()->role_id == 1 || auth()->user()->role_id == 5) {
                $fees_payments = SmFeesPayment::with('studentInfo')->where('active_status', 1)->orderby('id', 'DESC')->get();

            } else {
                $fees_payments = SmFeesPayment::with('studentInfo')->where('created_by', auth()->user()->id)->where('active_status', 1)->orderby('id', 'DESC')->get();
            }

            $classes = SmClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.feesCollection.search_fees_payment', ['classes' => $classes, 'fees_payments' => $fees_payments]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesPaymentSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $classes = SmClass::where('active_status', 1)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            $old_fees_payments = DB::table('sm_fees_payments')
                ->join('sm_students', 'sm_fees_payments.student_id', '=', 'sm_students.id')
                ->join('sm_fees_masters', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_masters.fees_type_id')
                ->join('sm_fees_groups', 'sm_fees_masters.fees_group_id', '=', 'sm_fees_groups.id')
                ->join('sm_fees_types', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_types.id')
                ->join('sm_classes', 'sm_students.class_id', '=', 'sm_classes.id')
                ->join('sm_sections', 'sm_students.section_id', '=', 'sm_sections.id')
                ->where('sm_students.class_id', $request->class)
                ->where('sm_students.section_id', $request->section)
                ->orwhere('sm_students.full_name', '%'.@$request->keyword.'%')
                ->orwhere('sm_students.admission_no', '%'.@$request->keyword.'%')
                ->orwhere('sm_students.roll_no', '%'.@$request->keyword.'%')
                ->select('sm_fees_payments.*', 'sm_students.full_name', 'sm_classes.class_name', 'sm_fees_groups.name', 'sm_fees_types.name as fees_type_name')
                ->where('sm_fees_payments.school_id', Auth::user()->school_id)->get();

            $student_ids = [];

            foreach ($old_fees_payments as $old_fee_payment) {
                $student_ids[] = $old_fee_payment->student_id;
            }

            if (auth()->user()->role_id == 1 || auth()->user()->role_id == 5) {
                $fees_payments = SmFeesPayment::with('studentInfo')->whereIn('student_id', $student_ids)->where('active_status', 1)->orderby('id', 'DESC')->get();
            } else {
                $fees_payments = SmFeesPayment::with('studentInfo')->whereIn('student_id', $student_ids)->where('created_by', auth()->user()->id)->orderby('id', 'DESC')->where('active_status', 1)->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.feesCollection.search_fees_payment', ['fees_payments' => $fees_payments, 'classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function searchFeesDue(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            //
            $students = SmStudent::where('active_status', 1)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();

            $fees_dues = [];
            foreach ($students as $student) {
                $fees_assigns = SmFeesAssign::where('student_id', $student->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->whereHas('feesGroupMaster', function ($q) {
                        return $q->whereDate('date', '<', date('Y-m-d'));
                    })
                    ->where('fees_amount', '>', 0)->get();

                foreach ($fees_assigns as $fee_assign) {
                    $fees_dues[] = $fee_assign;
                }

            }

            return view('backEnd.feesCollection.search_fees_due', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_dues' => $fees_dues]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesDueSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'fees_group' => 'required',
            'class' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $fees_group = explode('-', $request->fees_group);
            $fees_master = SmFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('school_id', Auth::user()->school_id)->first();
            $fees_master = SmFeesMaster::select('id', 'amount')->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();

            if ($request->section == '') {
                $students = SmStudent::where('class_id', $request->class)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            } else {
                $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            }

            $fees_dues = [];
            foreach ($students as $student) {

                $fees_assigns = SmFeesAssign::with('feesGroupMaster')->where('student_id', $student->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->whereHas('feesGroupMaster', function ($q) use ($fees_group) {
                        return $q
                            // ->whereDate('date', '<', date('Y-m-d'))
                            ->where('fees_group_id', $fees_group[0])->where('fees_type_id', $fees_group[1]);
                    })
                    ->where('fees_amount', '>', 0)->get();

                foreach ($fees_assigns as $fee_assign) {
                    $fees_dues[] = $fee_assign;
                }

            }

            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $class_id = $request->class;
            $fees_group_id = $fees_group[1];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters;
                $data['fees_dues'] = $fees_dues;
                $data['class_id'] = $class_id;
                $data['fees_group_id'] = $fees_group_id;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.search_fees_due', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_dues' => $fees_dues, 'class_id' => $class_id, 'fees_group_id' => $fees_group_id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function sendDuesFeesEmail(Request $request)
    {
        try {

            if (property_exists($request, 'send_email') && $request->send_email !== null) {

                $systemEmail = SmsTemplate::first();
                foreach ($request->student_list as $student) {
                    $student_detail = SmStudent::where('id', $student)->first();
                    $fees_info['dues_fees'] = $request->dues_amount[$student];
                    $fees_info['fees_master'] = $request->fees_master;

                    $compact['student_detail'] = $student_detail;
                    $compact['fees_info'] = $fees_info;

                    if ($student_detail->email !== '') {

                        send_mail($student_detail->email, $student_detail->full_name, 'Dues Payment', 'backEnd.feesCollection.dues_fees_email');

                    }

                    $parent_detail = SmParent::where('id', $student_detail->parent_id)->first();

                    if ($parent_detail->guardians_email !== '') {
                        send_mail($parent_detail->guardians_email, $parent_detail->guardians_name, 'Dues Payment', 'backEnd.feesCollection.dues_fees_email');

                    }
                }

            } elseif (property_exists($request, 'send_sms') && $request->send_sms !== null) {

                foreach ($request->student_list as $student) {

                    $student_detail = SmStudent::find($student);
                    $parent_detail = SmParent::find($student_detail->parent_id);

                    $fees_info['dues_fees'] = $request->dues_amount[$student];
                    $fees_info['fees_master'] = $request->fees_master;

                    $email_template = SmsTemplate::where('id', 1)->first();

                    $body = $email_template->dues_fees_message_sms;

                    $chars = preg_split('/[\s,]+/', $body, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                    foreach ($chars as $char) {
                        if (mb_strstr($char[0], '[')) {

                            $str = str_replace('[', '', $char);
                            $str = str_replace(']', '', $str);
                            $str = str_replace('.', '', $str);

                            $custom_array[$char] = SmsTemplate::getValueByStringDuesFees($student_detail, $str, $fees_info);
                        }

                    }

                    foreach ($custom_array as $key => $value) {
                        $body = str_replace($key, $value, $body);
                    }

                    $activeSmsGateway = SmSmsGateway::where('active_status', 1)->first();

                    if ($activeSmsGateway->gateway_name == 'Twilio') {

                        $account_id = $activeSmsGateway->twilio_account_sid; // Your Account SID from www.twilio.com/console
                        $auth_token = $activeSmsGateway->twilio_authentication_token; // Your Auth Token from www.twilio.com/console
                        $from_phone_number = $activeSmsGateway->twilio_registered_no;

                        $client = new Client($account_id, $auth_token);

                        // student sms

                        if ($student_detail->mobile !== '') {
                            $result = $client->messages->create($student_detail->mobile, ['from' => $from_phone_number, 'body' => $body]);
                            $message = $result;
                        }

                        // guardian sms
                        if ($parent_detail->guardians_mobile !== '') {
                            $result = $client->messages->create($parent_detail->guardians_mobile, ['from' => $from_phone_number, 'body' => $body]);
                            $message = $result;
                        }

                    } elseif ($activeSmsGateway->gateway_name == 'Himalayasms') {

                        if ($student_detail->mobile !== '') {

                            $client = new HttpClient();
                            $request = $client->get('https://sms.techhimalaya.com/base/smsapi/index.php', [
                                'query' => [
                                    'key' => $activeSmsGateway->himalayasms_key,
                                    'senderid' => $activeSmsGateway->himalayasms_senderId,
                                    'campaign' => $activeSmsGateway->himalayasms_campaign,
                                    'routeid' => $activeSmsGateway->himalayasms_routeId,
                                    'contacts' => $student_detail->mobile,
                                    'msg' => $body,
                                    'type' => 'text',
                                ],
                                'http_errors' => false,
                            ]);

                            $result = $request->getBody();
                        }

                        if ($parent_detail->fathers_mobile !== '') {

                            $client = new HttpClient();
                            $request = $client->get('https://sms.techhimalaya.com/base/smsapi/index.php', [
                                'query' => [
                                    'key' => $activeSmsGateway->himalayasms_key,
                                    'senderid' => $activeSmsGateway->himalayasms_senderId,
                                    'campaign' => $activeSmsGateway->himalayasms_campaign,
                                    'routeid' => $activeSmsGateway->himalayasms_routeId,
                                    'contacts' => $parent_detail->fathers_mobile,
                                    'msg' => $body,
                                    'type' => 'text',
                                ],
                                'http_errors' => false,
                            ]);

                            $result = $request->getBody();
                        }

                    } elseif ($activeSmsGateway->gateway_name == 'Msg91') {

                        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
                        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
                        $msg91_route = $activeSmsGateway->msg91_route;
                        $msg91_country_code = $activeSmsGateway->msg91_country_code;

                        if ($student_detail->mobile !== '') {

                            $curl = curl_init();

                            $url = 'https://api.msg91.com/api/sendhttp.php?mobiles='.$student_detail->mobile.'&authkey='.$msg91_authentication_key_sid.'&route='.$msg91_route.'&sender='.$msg91_sender_id.'&message='.$body.'&country=91';

                            curl_setopt_array($curl, [
                                CURLOPT_URL => $url,
                                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                            ]);
                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                        }

                        if ($parent_detail->guardians_mobile !== '') {

                            $curl = curl_init();

                            $url = 'https://api.msg91.com/api/sendhttp.php?mobiles='.$parent_detail->guardians_mobile.'&authkey='.$msg91_authentication_key_sid.'&route='.$msg91_route.'&sender='.$msg91_sender_id.'&message='.$body.'&country=91';

                            curl_setopt_array($curl, [
                                CURLOPT_URL => $url,
                                CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                            ]);
                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);

                        }
                    }

                }

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function feesStatemnt(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_statment', ['classes' => $classes, 'fees_masters' => $fees_masters]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $fees_masters = SmFeesMaster::select('fees_group_id')->where('active_status', 1)->distinct('fees_group_id')->where('school_id', Auth::user()->school_id)->get();
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
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['fees_masters'] = $fees_masters->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['applied_discount'] = $applied_discount;
                $data['student'] = $student;
                $data['class_id'] = $class_id;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_statment', ['classes' => $classes, 'fees_masters' => $fees_masters, 'fees_assigneds' => $fees_assigneds, 'fees_discounts' => $fees_discounts, 'applied_discount' => $applied_discount, 'student' => $student, 'class_id' => $class_id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function balanceFeesReport(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($classes, null);
            }

            return view('backEnd.feesCollection.balance_fees_report', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function balanceFeesSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'class' => 'required',
            'section' => 'required',
        ]);
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id', Auth::user()->school_id)->get();
            $balance_students = [];
            $fees_masters = SmFeesMaster::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            foreach ($students as $student) {
                $total_balance = 0;
                $total_discount = 0;
                $total_amount = 0;
                $master_ids = [];
                foreach ($fees_masters as $fee_master) {

                    $due_date = strtotime($fee_master->date);
                    $now = strtotime(date('Y-m-d'));
                    if ($due_date > $now) {
                        continue;
                    }

                    $master_ids[] = $fee_master->id;
                    $fees_assign = SmFeesAssign::where('student_id', $student->id)->where('fees_master_id', $fee_master->id)->where('school_id', Auth::user()->school_id)->first();
                    if ($fees_assign !== '') {
                        $discount_amount = SmFeesPayment::where('active_status', 1)->where('student_id', $student->id)->where('fees_type_id', $fee_master->fees_type_id)->sum('discount_amount');
                        $balance = SmFeesPayment::where('active_status', 1)->where('student_id', $student->id)->where('fees_type_id', $fee_master->fees_type_id)->sum('amount');
                        $total_balance += $balance;
                        $total_discount += $discount_amount;
                        $total_amount += $fee_master->amount;

                    }
                }

                $total_paid = $total_balance + $total_discount;
                if ($total_amount > $total_paid) {

                    $balance_students[] = $student;
                }
            }

            // return $master_ids;
            $class_id = $request->class;
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['balance_students'] = $balance_students;
                $data['class_id'] = $class_id;

                return ApiBaseMethod::sendResponse($data, null);
            }

            // return $balance_students;
            $clas = SmClass::find($request->class);

            return view('backEnd.feesCollection.balance_fees_report', ['classes' => $classes, 'balance_students' => $balance_students, 'class_id' => $class_id, 'clas' => $clas]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoice($sid, $pid, $faid)
    {
        try {
            return view('backEnd.feesCollection.fees_collect_invoice');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGroupPrint($id): void
    {
        $fees_assigned = SmFeesAssign::find($id);
        SmStudent::find($fees_assigned->student_id);
    }

    public function feesPaymentPrint($id, $group)
    {
        try {
            // $payment = SmFeesPayment::find($id);
            if (checkAdmin() == true) {
                $payment = SmFeesPayment::find($id);
            } else {
                $payment = SmFeesPayment::where('active_status', 1)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $student = SmStudent::find($payment->student_id);
            $pdf = Pdf::loadView('backEnd.feesCollection.fees_payment_print', ['payment' => $payment, 'group' => $group, 'student' => $student]);

            return $pdf->stream(date('d-m-Y').'-'.$student->full_name.'-fees-payment-details.pdf');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesPaymentInvoicePrint($id, $s_id)
    {
        try {
            set_time_limit(2700);
            $groups = explode('-', $id);
            $student = SmStudent::find($s_id);
            foreach ($groups as $group) {
                $fees_assigneds[] = SmFeesAssign::find($group);
            }

            $parent = DB::table('sm_parents')->where('id', $student->parent_id)->where('school_id', Auth::user()->school_id)->first();

            $unapplied_discount_amount = SmFeesAssignDiscount::where('student_id', $s_id)->where('school_id', Auth::user()->school_id)->sum('unapplied_amount');

            return view('backEnd.feesCollection.fees_payment_invoice_print')->with(['fees_assigneds' => $fees_assigneds, 'student' => $student, 'unapplied_discount_amount' => $unapplied_discount_amount, 'parent' => $parent]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGroupsPrint($id, $s_id)
    {
        try {
            $groups = explode('-', $id);
            $student = SmStudent::find($s_id);
            foreach ($groups as $group) {
                $fees_assigneds[] = SmFeesAssign::find($group);
            }

            $pdf = Pdf::loadView('backEnd.feesCollection.fees_groups_print', ['fees_assigneds' => $fees_assigneds, 'student' => $student]);

            return $pdf->stream(date('d-m-Y').'-'.$student->full_name.'-fees-groups-details.pdf');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function transactionReport(Request $request)
    {
        try {
            $classes = SmClass::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }

            return view('backEnd.feesCollection.transaction_report', ['classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function transactionReportSearch(Request $request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';

        try {
            $classes = SmClass::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            if ($request->date_range) {
                $date_from = new DateTimeImmutable(trim($rangeArr[0]));
                $date_to = new DateTimeImmutable(trim($rangeArr[1]));
            }

            if ($request->date_range) {
                if ($request->class) {
                    $students = SmStudent::where('class_id', $request->class)
                        ->where('school_id', Auth::user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->get();

                    $fees_payments = SmFeesPayment::where('active_status', 1)->whereIn('student_id', $students->pluck('id'))
                        ->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

                    $fees_payments = $fees_payments->distinct('student_id');
                } else {
                    $fees_payments = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();

                    $fees_payments = $fees_payments->distinct('student_id');
                }
            }

            if ($request->class && $request->section) {

                $students = SmStudent::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();

                $fees_payments = SmFeesPayment::where('active_status', 1)->whereIn('student_id', $students->pluck('id'))
                    ->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
                $fees_payments = $fees_payments->distinct('student_id');

            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_payments'] = $fees_payments->toArray();
                $data['add_incomes'] = $add_incomes->toArray();
                $data['add_expenses'] = $add_expenses->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.transaction_report', ['fees_payments' => $fees_payments, 'classes' => $classes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function studentFineReport(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }

            return view('backEnd.reports.student_fine_report');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function studentFineReportSearch(Request $request)
    {
        try {
            $date_from = date('Y-m-d', strtotime($request->date_from));
            $date_to = date('Y-m-d', strtotime($request->date_to));
            $fees_payments = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)->where('payment_date', '<=', $date_to)->where('fine', '!=', 0)->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_payments, null);
            }

            return view('backEnd.reports.student_fine_report', ['fees_payments' => $fees_payments]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    //
    public function bankPaymentSlip()
    {
        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $bank_slips = SmBankPaymentSlip::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('approve_status', 0)->orderBy('id', 'desc')->get();

            return view('backEnd.feesCollection.bank_payment_slip', ['classes' => $classes, 'bank_slips' => $bank_slips]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function bankPaymentSlipSearch(Request $request)
    {
        $request->all();

        try {
            $bank_slips = SmBankPaymentSlip::query();
            if ($request->class !== '') {
                $bank_slips->where('class_id', $request->class);
            }

            if ($request->section !== '') {
                $bank_slips->where('section_id', $request->section);
            }

            if ($request->payment_date !== '') {
                $date = strtotime($request->payment_date);
                $newformat = date('Y-m-d', $date);
                $bank_slips->where('date', $newformat);
            }

            if ($request->approve_status !== '') {
                $bank_slips->where('approve_status', $request->approve_status);
            }

            $all_bank_slips = $bank_slips->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->orderBy('id', 'desc')->get();

            $date = $request->payment_date;
            $class_id = $request->class;
            $approve_status = $request->approve_status;
            $section_id = $request->section;
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $sections = SmSection::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.feesCollection.bank_payment_slip', ['all_bank_slips' => $all_bank_slips, 'classes' => $classes, 'sections' => $sections, 'date' => $date, 'class_id' => $class_id, 'section_id' => $section_id, 'approve_status' => $approve_status]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function rejectFeesPayment(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'payment_reject_reason' => 'required',
        ]);
        if ($validator->fails()) {
            Toastr::warning('Required Fill Missing', 'Failed');

            return redirect()->back();
        }

        try {

            if (checkAdmin() == true) {
                $bank_payment = SmBankPaymentSlip::find($request->id);
            } else {
                $bank_payment = SmBankPaymentSlip::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $systemEmail = SmsTemplate::first();
            $student = SmStudent::find($bank_payment->student_id);
            $parent = SmParent::find($student->parent_id);

            if ($bank_payment) {

                $bank_payment->reason = $request->payment_reject_reason;
                $bank_payment->approve_status = 2;
                $result = $bank_payment->save();

                if ($result) {
                    $notification = new SmNotification();
                    $notification->role_id = 2;
                    $notification->message = 'Bank Payment Rejected -('.@$bank_payment->feesType->name.')';
                    $notification->date = date('Y-m-d');
                    $notification->user_id = $student->user_id;
                    $notification->url = 'student-fees';
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = getAcademicId();
                    $notification->save();

                    try {
                        $reciver_email = $student->full_name;
                        $receiver_name = $student->email;
                        $subject = 'Bank Payment Rejected';
                        $view = 'backEnd.feesCollection.bank_payment_reject_student';
                        $compact['data'] = [
                            'note' => $bank_payment->reason,
                            'date' => dateConvert($notification->created_at),
                            'student_name' => $student->full_name,
                        ];
                        send_mail($reciver_email, $receiver_name, $subject, $view);
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }

                    $notification = new SmNotification();
                    $notification->role_id = 3;
                    $notification->message = 'Bank Payment Rejected -('.@$bank_payment->feesType->name.')';
                    $notification->date = date('Y-m-d');
                    $notification->user_id = $parent->user_id;
                    $notification->url = 'parent-fees/'.$student->id;
                    $notification->school_id = Auth::user()->school_id;
                    $notification->academic_id = getAcademicId();
                    $notification->save();

                    try {
                        $reciver_email = $student->email;
                        $receiver_name = $student->full_name;
                        $subject = 'Bank Payment Rejected';
                        $view = 'backEnd.feesCollection.bank_payment_reject_student';
                        $compact['data'] = [
                            'note' => $bank_payment->reason,
                            'date' => dateConvert($notification->created_at),
                            'student_name' => $student->full_name,
                        ];
                        send_mail($reciver_email, $receiver_name, $subject, $view);
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }

                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();

            }

        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        return null;

    }

    public function approveFeesPayment(Request $request)
    {
        try {

            if (checkAdmin() == true) {
                $bank_payment = SmBankPaymentSlip::find($request->id);
            } else {
                $bank_payment = SmBankPaymentSlip::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $get_master_id = SmFeesMaster::join('sm_fees_assigns', 'sm_fees_assigns.fees_master_id', '=', 'sm_fees_masters.id')
                ->where('sm_fees_masters.fees_type_id', $bank_payment->fees_type_id)
                ->where('sm_fees_assigns.student_id', $bank_payment->student_id)->first();

            $fees_assign = SmFeesAssign::where('fees_master_id', $get_master_id->fees_master_id)->where('student_id', $bank_payment->student_id)->where('school_id', Auth::user()->school_id)->first();

            // return $bank_payment;

            if ($bank_payment->amount > $fees_assign->fees_amount) {
                Toastr::warning('Due amount less than bank payment', 'Warning');

                return redirect()->back();
            }

            $user = Auth::user();
            $smFeesPayment = new SmFeesPayment();
            $smFeesPayment->student_id = $bank_payment->student_id;
            $smFeesPayment->fees_type_id = $bank_payment->fees_type_id;
            $smFeesPayment->discount_amount = 0;
            $smFeesPayment->fine = 0;
            $smFeesPayment->amount = $bank_payment->amount;
            $smFeesPayment->assign_id = $bank_payment->assign_id;
            $smFeesPayment->payment_date = date('Y-m-d', strtotime($bank_payment->date));
            $smFeesPayment->payment_mode = $bank_payment->payment_mode;
            $smFeesPayment->bank_id = $bank_payment->payment_mode == 'bank' ? $bank_payment->bank_id : null;
            $smFeesPayment->created_by = $user->id;
            $smFeesPayment->note = $bank_payment->note;
            $smFeesPayment->academic_id = getAcademicId();
            $smFeesPayment->school_id = Auth::user()->school_id;
            $result = $smFeesPayment->save();
            $bank_payment->approve_status = 1;
            $bank_payment->save();

            $payment_mode_name = ucwords($bank_payment->payment_mode);
            $payment_method = SmPaymentMethhod::where('method', $payment_mode_name)->first();
            $income_head = generalSetting();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Fees Collect';
            $smAddIncome->date = date('Y-m-d', strtotime($bank_payment->date));
            $smAddIncome->amount = $bank_payment->amount;
            $smAddIncome->fees_collection_id = $smFeesPayment->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->income_head_id = $income_head->income_head_id;
            $smAddIncome->payment_method_id = $payment_method->id;
            if ($payment_method->id == 3) {
                $smAddIncome->account_id = $bank_payment->bank_id;
            }

            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            $smAddIncome->academic_id = getAcademicId();
            $smAddIncome->save();

            if ($payment_method->id == 3) {
                $bank = SmBankAccount::where('id', $bank_payment->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $bank_payment->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $bank_payment->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = 'Fees Payment';
                $smBankStatement->payment_date = date('Y-m-d', strtotime($bank_payment->date));
                $smBankStatement->bank_id = $bank_payment->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $payment_method->id;
                $smBankStatement->fees_payment_id = $smFeesPayment->id;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($bank_payment->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            // $fees_assign=SmFeesAssign::where('fees_master_id',$get_master_id->fees_master_id)->where('student_id',$bank_payment->student_id)->first();
            $fees_assign->fees_amount -= $bank_payment->amount;
            $fees_assign->save();

            $bank_slips = SmBankPaymentSlip::query();
            $bank_slips->where('class_id', $request->class);
            if ($request->section !== '') {
                $bank_slips->where('section_id', $request->section);
            }

            if ($request->payment_date !== '') {
                $date = strtotime($request->payment_date);
                $newformat = date('Y-m-d', $date);

                $bank_slips->where('date', $newformat);
            }

            $bank_slips = $bank_slips->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->orderBy('id', 'desc')->get();
            $date = $request->payment_date;
            $class_id = $request->class;
            $section_id = $request->section;
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $sections = SmSection::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $student = SmStudent::find($bank_payment->student_id);

            $notification = new SmNotification;
            $notification->user_id = $student->user_id;
            $notification->role_id = 2;
            $notification->date = date('Y-m-d');
            $notification->message = app('translator')->get('lang.fees_approved');
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = getAcademicId();
            $notification->save();

            try {
                $user = User::find($student->user_id);
                Notification::send($user, new FeesApprovedNotification($notification));
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            $parent = SmParent::find($student->parent_id);
            $notification = new SmNotification();
            $notification->role_id = 3;
            $notification->message = app('translator')->get('lang.fees_approved_for_child');
            $notification->date = date('Y-m-d');
            $notification->user_id = $parent->user_id;
            $notification->url = '';
            $notification->school_id = Auth::user()->school_id;
            $notification->academic_id = getAcademicId();
            $notification->save();

            try {
                $user = User::find($parent->user_id);
                Notification::send($user, new FeesApprovedNotification($notification));
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('bank-payment-slip');
            // return view('backEnd.feesCollection.bank_payment_slip', compact('bank_slips','classes','sections','date','class_id','section_id'));
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function fineReport()
    {
        $classes = SmClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

        return view('backEnd.accounts.fine_report', ['classes' => $classes]);
    }

    public function fineReportSearch(Request $request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';

        try {
            $classes = SmClass::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            if ($request->date_range) {
                $date_from = new DateTimeImmutable(trim($rangeArr[0]));
                $date_to = new DateTimeImmutable(trim($rangeArr[1]));
            }

            if ($request->date_range) {
                $fine_info = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $fine_info = $fine_info->distinct('student_id');
            }

            if ($request->class) {
                $students = SmStudent::where('class_id', $request->class)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();

                $fine_info = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('school_id', Auth::user()->school_id)
                    ->whereIn('student_id', $students)
                    ->get();
                $fine_info = $fine_info->distinct('student_id');

            }

            if ($request->class && $request->section) {

                $students = StudentRecord::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->pluck('student_id')->unique()->toArray();

                $fine_info = SmFeesPayment::where('active_status', 1)->where('payment_date', '>=', $date_from)
                    ->where('payment_date', '<=', $date_to)
                    ->where('school_id', Auth::user()->school_id)
                    ->whereIn('student_id', $students)
                    ->get();

                $fine_info = $fine_info->groupBy('student_id');
            }

            return view('backEnd.accounts.fine_report', ['classes' => $classes, 'fine_info' => $fine_info]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    // added by nayem fees edit delete

    public function editFeesPayment($id)
    {

        try {
            $fees_payment = SmFeesPayment::find($id);

            if (auth()->user()->role_id !== 1 && $fees_payment->created_by !== auth()->user()->id) {
                Toastr::error('Payment recieved Other person,You Can not Edit', 'Failed');

                return redirect()->back();
            }

            $data['bank_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Bank')->where('school_id', Auth::user()->school_id)->first();
            $data['cheque_info'] = SmPaymentGatewaySetting::where('gateway_name', 'Cheque')->where('school_id', Auth::user()->school_id)->first();

            $banks = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();
            $method['bank_info'] = SmPaymentMethhod::where('method', 'Bank')->where('school_id', Auth::user()->school_id)->first();
            $method['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')->where('school_id', Auth::user()->school_id)->first();

            return view('backEnd.feesCollection.edit_fees_payment_modal', ['fees_payment' => $fees_payment, 'data' => $data, 'method' => $method, 'banks' => $banks]);

        } catch (Throwable $throwable) {
            // throw $th;
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function updateFeesPayment(Request $request)
    {

        try {

            $assignCourseFees = SmFeesAssign::find($request->fees_assign_id);
            $fees_master = SmFeesMaster::find($assignCourseFees->fees_master_id);
            $amount_check = $assignCourseFees->fees_amount - $request->amount;

            if ($fees_master->amount <= $request->amount) {
                Toastr::warning('Payment amount will not greater than fees assign amount', 'Warning');

                return redirect()->back();
            }

            if ($amount_check < 0) {
                $payment = SmFeesPayment::find($request->fees_payment_id);
                $payment->payment_mode = $request->payment_mode;
                $payment->bank_id = $request->payment_mode == 'bank' ? $request->bank_id : null;
                $payment->save();
                Toastr::warning('Fees Payment already full paid, Can not Change Amount', 'Warning');

                return redirect()->back();

            }

            if ($assignCourseFees->fees_amount == 0) {

                $pre_amount = $assignCourseFees->fees_amount;

            } else {

                $diff_amount = $request->amount - $request->pre_amount;

                $pre_amount = $assignCourseFees->fees_amount - $diff_amount;

            }

            $assignCourseFees->fees_amount = $pre_amount;
            $result = $assignCourseFees->save();
            if ($result) {
                $payment = SmFeesPayment::find($request->fees_payment_id);
                $payment->amount = $request->amount;
                $payment->payment_mode = $request->payment_mode;
                $payment->bank_id = $request->payment_mode == 'bank' ? $request->bank_id : null;
                $payment->save();
            } else {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
