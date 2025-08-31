<?php

namespace App\Http\Controllers\Admin\Hr;

use Exception;
use Throwable;
use App\SmStaff;
use Carbon\Carbon;
use App\SmAddExpense;
use App\SmBankAccount;
use App\SmLeaveDefine;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use App\SmStaffAttendence;
use App\SmHrPayrollGenerate;
use Illuminate\Http\Request;
use App\SmHrPayrollEarnDeduc;
use App\SmLeaveDeductionInfo;
use App\Models\PayrollPayment;
use App\Traits\NotificationSend;
use Modules\Lms\Entities\Course;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Lms\Entities\CoursePurchaseLog;
use Modules\RolePermission\Entities\InfixRole;

class SmPayrollController extends Controller
{
    use NotificationSend;

    public function index(Request $request)
    {

        /*
        try {
        */
            $data['roles'] = InfixRole::where('is_saas', 0)
                ->where('active_status', '=', '1')
                ->where('id', '!=', 1)->where('id', '!=', 2)
                ->where('id', '!=', 3)->where('id', '!=', 10)
                ->where(function ($q): void {
                    $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
                })
                ->orderBy('name', 'asc')
                ->select(['name', 'type', 'id'])
                ->get();
            $data['role_id'] = $request->role_id;
            $data['payroll_month'] = $request->payroll_month;
            $data['payroll_year'] = $request->payroll_year;
            if ($request->role_id) {
                $data['staffs'] = SmStaff::where('active_status', '=', '1')
                    ->whereRole($request->role_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->with([
                        'departments' => function ($query) {
                            return $query->select(['name', 'id']);
                        },
                        'designations' => function ($query) {
                            return $query->select(['title', 'id', 'active_status']);

                        },
                        'payrollStatus',
                        'payrollStatus.payrollPayments',
                        'roles' => function ($query) {
                            return $query->select(['name', 'id', 'active_status']);
                        },
                    ])->get();
            }
            return view('backEnd.humanResource.payroll.index')->with($data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function searchStaffPayr(Request $request)
    {

        $request->validate([
            'role_id' => 'required',
            'payroll_month' => 'required',
            'payroll_year' => 'required',

        ], [
            'role_id.required' => 'The role field is required.',
        ]);

        /*
        try {
        */
            $role_id = $request->role_id;
            $payroll_month = $request->payroll_month;
            $payroll_year = $request->payroll_year;

            $staffs = SmStaff::where('active_status', '=', '1')->whereRole($role_id)->where('school_id', Auth::user()->school_id)->get();

            $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            return view('backEnd.humanResource.payroll.index', ['staffs' => $staffs, 'roles' => $roles, 'payroll_month' => $payroll_month, 'payroll_year' => $payroll_year, 'role_id' => $role_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function generatePayroll(Request $request, $id, $payroll_month, string $payroll_year)
    {
        /*
        try {
        */
            $staffDetails = SmStaff::find($id);
            // return $staffDetails;
            $month = date('m', strtotime($payroll_month));

            $attendances = SmStaffAttendence::where('staff_id', $id)->where('attendence_date', 'like', $payroll_year.'-'.$month.'%')->where('school_id', Auth::user()->school_id)->get();

            $staff_leaves = SmLeaveDefine::where('user_id', $staffDetails->user_id)->where('role_id', $staffDetails->role_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $staff_leave_deduct_days = SmLeaveDeductionInfo::where('staff_id', $id)->where('pay_year', $payroll_year)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get()->sum('extra_leave');

            // return $payroll_year;
            foreach ($staff_leaves as $staff_leaf) {
                //  $approved_leaves = SmLeaveRequest::approvedLeave($staff_leave->id);
                $remaining_days = $staff_leaf->days - $staff_leaf->remainingDays;
                $extra_Leave_days = $remaining_days < 0 ? $staff_leaf->remainingDays - $staff_leaf->days : 0;
            }

            $extra_days = $staff_leave_deduct_days !== '' ? @$extra_Leave_days - @$staff_leave_deduct_days : @$extra_Leave_days;

            // return $extra_days;

            // $approved_leave = SmLeaveRequest::where('staff_id', $id)->where('active_status',1)->where('approve_status','A')->where('school_id', Auth::user()->school_id)->get();
            // return $extra_days;
            $p = 0;
            $l = 0;
            $a = 0;
            $f = 0;
            $h = 0;
            foreach ($attendances as $attendance) {
                if ($attendance->attendence_type == 'P') {
                    $p++;
                } elseif ($attendance->attendence_type == 'L') {
                    $l++;
                } elseif ($attendance->attendence_type == 'A') {
                    $a++;
                } elseif ($attendance->attendence_type == 'F') {
                    $f++;
                } elseif ($attendance->attendence_type == 'H') {
                    $h++;
                }
            }

            // For Teacher Commission
            if (moduleStatusCheck('Lms') == true) {
                $months = [
                    'January' => 1,
                    'February' => 2,
                    'March' => 3,
                    'April' => 4,
                    'May' => 5,
                    'June' => 6,
                    'July' => 7,
                    'August' => 8,
                    'September' => 9,
                    'October' => 10,
                    'November' => 11,
                    'December' => 12,
                ];

                $monthNumber = $months[$payroll_month];

                $data['courses'] = Course::where('instructor_id', $id)->get(['id']);
                $data['courseIds'] = $data['courses']->pluck('id')->toArray();
                $data['totalCourse'] = $data['courses']->count();
                $totalSellCourse = CoursePurchaseLog::whereIn('course_id', $data['courseIds'])->where('instructor_id', $id);
                $data['totalSellCourseCount'] = $totalSellCourse->count();
                $data['thisMonthSell'] = CoursePurchaseLog::where('instructor_id', $id)->whereIn('course_id', $data['courseIds'])->whereMonth('created_at', $monthNumber)->count();
                $totalSellAmount = $totalSellCourse->sum('amount');
                $teacher_commission = courseSetting()->teacher_commission;
                $data['totalRevenue'] = earnRevenue($totalSellAmount, $teacher_commission);

                return view('backEnd.humanResource.payroll.generatePayroll', ['staffDetails' => $staffDetails, 'payroll_month' => $payroll_month, 'payroll_year' => $payroll_year, 'p' => $p, 'l' => $l, 'a' => $a, 'f' => $f, 'h' => $h, 'extra_days' => $extra_days])->with($data);
            }

            // End Of teacher commission
            return view('backEnd.humanResource.payroll.generatePayroll', ['staffDetails' => $staffDetails, 'payroll_month' => $payroll_month, 'payroll_year' => $payroll_year, 'p' => $p, 'l' => $l, 'a' => $a, 'f' => $f, 'h' => $h, 'extra_days' => $extra_days]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function savePayrollData(Request $request)
    {
        // return $request->all();
        $request->validate([
            'net_salary' => 'required',
        ]);

       
        try {
            
            $smHrPayrollGenerate = new SmHrPayrollGenerate();
            $smHrPayrollGenerate->staff_id = $request->staff_id;
            $smHrPayrollGenerate->payroll_month = $request->payroll_month;
            $smHrPayrollGenerate->payroll_year = $request->payroll_year;
            $smHrPayrollGenerate->basic_salary = $request->basic_salary;
            $smHrPayrollGenerate->total_earning = $request->total_earning;
            $smHrPayrollGenerate->total_deduction = $request->total_deduction;
            $smHrPayrollGenerate->gross_salary = $request->final_gross_salary;
            $smHrPayrollGenerate->tax = $request->tax;
            $smHrPayrollGenerate->net_salary = $request->net_salary;
            $smHrPayrollGenerate->payroll_status = 'G';
            $smHrPayrollGenerate->created_by = Auth()->user()->id;
            $smHrPayrollGenerate->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smHrPayrollGenerate->un_academic_id = getAcademicId();
            } else {
                $smHrPayrollGenerate->academic_id = getAcademicId();
            }

            $result = $smHrPayrollGenerate->save();
            $smHrPayrollGenerate->toArray();
            
            $data['teacher_name'] = $smHrPayrollGenerate->staffDetails->full_name;
            $this->sent_notifications('Staff_Payroll', (array) $smHrPayrollGenerate->staffDetails->user_id, $data, ['Teacher']);

            if ($request->leave_deduction > 0) {
                $smLeaveDeductionInfo = new SmLeaveDeductionInfo;
                $smLeaveDeductionInfo->staff_id = $request->staff_id;
                $smLeaveDeductionInfo->payroll_id = $smHrPayrollGenerate->id;
                $smLeaveDeductionInfo->extra_leave = $request->extra_leave_taken;
                $smLeaveDeductionInfo->salary_deduct = $request->leave_deduction;
                $smLeaveDeductionInfo->pay_month = $request->payroll_month;
                $smLeaveDeductionInfo->pay_year = $request->payroll_year;
                $smLeaveDeductionInfo->created_by = Auth()->user()->id;
                $smLeaveDeductionInfo->school_id = Auth::user()->school_id;
                if (moduleStatusCheck('University')) {
                    $smLeaveDeductionInfo->un_academic_id = getAcademicId();
                } else {
                    $smLeaveDeductionInfo->academic_id = getAcademicId();
                }

                $smLeaveDeductionInfo->save();
            }

            if ($result) {
               
                $earnings = count($request->get('earningsType', []));
                
                 
                
                for ($i = 0; $i < $earnings; $i++) {
                   
                    if (!empty($request->earningsType[$i]) && !empty($request->earningsValue[$i])) {
                         
                        // for teacher commission Lms module-abu nayem
                        if ($request->earningsType[0] == 'lms_balance' && moduleStatusCheck('Lms') == true) {
                            $payable_amount = $request->earningsValue[0];
                            $staff = SmStaff::findOrFail($request->staff_id);
                            $lms_balance = $staff->lms_balance;
                            if ($payable_amount > 0) {
                                $balance = $lms_balance - $payable_amount;
                                $staff->lms_balance = $balance;
                                $staff->save();
                            }
                        }

                        // end
                        $payroll_earn_deducs = new SmHrPayrollEarnDeduc;
                        $payroll_earn_deducs->payroll_generate_id = $smHrPayrollGenerate->id;
                        $payroll_earn_deducs->type_name = $request->earningsType[$i];
                        $payroll_earn_deducs->amount = $request->earningsValue[$i];
                        $payroll_earn_deducs->earn_dedc_type = 'E';
                        $payroll_earn_deducs->created_by = Auth()->user()->id;
                        $payroll_earn_deducs->school_id = Auth::user()->school_id;
                        if (moduleStatusCheck('University')) {
                            $payroll_earn_deducs->un_academic_id = getAcademicId();
                        } else {
                            $payroll_earn_deducs->academic_id = getAcademicId();
                        }

                        $result = $payroll_earn_deducs->save();
                    }
                }
                
              

                $deductions = count($request->get('deductionstype', []));
                for ($i = 0; $i < $deductions; $i++) {
                    if (! empty($request->deductionstype[$i]) && ! empty($request->deductionsValue[$i])) {

                        $payroll_earn_deducs = new SmHrPayrollEarnDeduc;
                        $payroll_earn_deducs->payroll_generate_id = $smHrPayrollGenerate->id;
                        $payroll_earn_deducs->type_name = $request->deductionstype[$i];
                        $payroll_earn_deducs->amount = $request->deductionsValue[$i];
                        $payroll_earn_deducs->earn_dedc_type = 'D';
                        $payroll_earn_deducs->school_id = Auth::user()->school_id;
                        if (moduleStatusCheck('University')) {
                            $payroll_earn_deducs->un_academic_id = getAcademicId();
                        } else {
                            $payroll_earn_deducs->academic_id = getAcademicId();
                        }

                        $result = $payroll_earn_deducs->save();
                    }
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->route('payroll', ['role_id' => $request->id, 'payroll_month' => $request->payroll_month, 'payroll_year' => $request->payroll_year]);
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        
    }

    public function paymentPayroll(Request $request, $id, $role_id)
    {
        /*
        try {
        */
            $chart_of_accounts = SmChartOfAccount::where('type', 'E')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $payrollDetails = SmHrPayrollGenerate::find($id);

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $account_id = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.humanResource.payroll.paymentPayroll', ['payrollDetails' => $payrollDetails, 'paymentMethods' => $paymentMethods, 'role_id' => $role_id, 'chart_of_accounts' => $chart_of_accounts, 'account_id' => $account_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function savePayrollPaymentData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_head_id' => 'required',
            'payment_mode' => 'required',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->messages());

            return redirect()->back();
        }

        /*
        try {
        */
            $payroll_month = $request->payroll_month;
            $payroll_year = $request->payroll_year;

            $payments = SmHrPayrollGenerate::find($request->payroll_generate_id);

            $payrollPayment = new PayrollPayment;
            $payrollPayment->sm_hr_payroll_generate_id = $request->payroll_generate_id;
            $payrollPayment->amount = $request->submit_amount;
            $payrollPayment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $payrollPayment->bank_id = $request->bank_id;
            $payrollPayment->payment_mode = $request->payment_mode;
            $payrollPayment->payment_method_id = $request->payment_method;
            $payrollPayment->note = $request->note;
            $payrollPayment->created_by = auth()->user()->id;
            $result = $payrollPayment->save();

            if ($payments->payrollPayments->sum('amount') >= $payments->net_salary || $request->submit_amount >= $payments->net_salary) {
                $payments->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $payments->payment_mode = $request->payment_mode;
                $payments->note = $request->note;
                $payments->payroll_status = 'P';
                $payments->updated_by = Auth()->user()->id;
                if (moduleStatusCheck('University')) {
                    $payments->un_academic_id = getAcademicId();
                } else {
                    $payments->academic_id = getAcademicId();
                }

                $result = $payments->update();
            }

            $leave_deduct = SmLeaveDeductionInfo::where('payroll_id', $request->payroll_generate_id)->first();
            if (! empty($leave_deduct)) {
                $leave_deduct->active_status = 1;
                $leave_deduct->save();
            }

            if ($result) {
                $smAddExpense = new SmAddExpense();
                $smAddExpense->name = 'Staff Payroll';
                $smAddExpense->expense_head_id = $request->expense_head_id;
                $smAddExpense->payroll_payment_id = $payrollPayment->id;
                $smAddExpense->payment_method_id = $request->payment_mode;
                if ($request->payment_mode == 3) {
                    $smAddExpense->account_id = $request->bank_id;
                }

                if (moduleStatusCheck('University')) {
                    $smAddExpense->un_academic_id = getAcademicId();
                } else {
                    $smAddExpense->academic_id = getAcademicId();
                }

                $smAddExpense->date = Carbon::now();
                $smAddExpense->amount = $request->submit_amount;
                $smAddExpense->description = 'Staff Payroll Payment';
                $smAddExpense->school_id = Auth::user()->school_id;
                $smAddExpense->save();
            }

            if ($request->payment_mode == 3) {
                $bank = SmBankAccount::where('id', $request->bank_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $request->submit_amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $request->submit_amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 0;
                $smBankStatement->details = 'Staff Payroll Payment';
                $smBankStatement->item_receive_id = $payments->id;
                $smBankStatement->payroll_payment_id = $payrollPayment->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $smBankStatement->bank_id = $request->bank_id;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $request->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($request->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            $data['staffs'] = SmStaff::where('active_status', '=', '1')->where('school_id', Auth::user()->school_id)->get();
            $data['roles'] = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $data['payroll_month'] = $payroll_month;
            $data['payroll_year'] = $payroll_year;

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('payroll', ['role_id' => $request->role_id, 'payroll_month' => $payroll_month, 'payroll_year' => $payroll_year]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewPayslip($id)
    {

        /*
        try {
        */
            $schoolDetails = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $payrollDetails = SmHrPayrollGenerate::find($id);

            $payrollEarnDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'E')->where('school_id', Auth::user()->school_id)->get();

            $payrollDedcDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'D')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.humanResource.payroll.viewPayslip', ['payrollDetails' => $payrollDetails, 'payrollEarnDetails' => $payrollEarnDetails, 'payrollDedcDetails' => $payrollDedcDetails, 'schoolDetails' => $schoolDetails]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function printPayslip($id)
    {

        /*
        try {
        */
            $schoolDetails = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $payrollDetails = SmHrPayrollGenerate::find($id);

            $payrollEarnDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'E')->where('school_id', Auth::user()->school_id)->get();

            $payrollDedcDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'D')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.humanResource.payroll.payslip_print', ['payrollDetails' => $payrollDetails, 'payrollEarnDetails' => $payrollEarnDetails, 'payrollDedcDetails' => $payrollDedcDetails, 'schoolDetails' => $schoolDetails]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function payrollReport(Request $request)
    {
        /*
        try {
        */
            $roles = InfixRole::select(['id', 'name'])->where('is_saas', 0)
                ->where('active_status', '=', '1')
                ->where('id', '!=', 1)
                ->where('id', '!=', 2)
                ->where('id', '!=', 3)
                ->where(function ($q): void {
                    $q->where('school_id', Auth::user()->school_id)
                        ->orWhere('type', 'System');
                })
                ->orderBy('name', 'asc')
                ->get();

            return view('backEnd.reports.payroll', ['roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function searchPayrollReport(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'payroll_month' => 'required',
            'payroll_year' => 'required',

        ]);
        /*
        try {
        */
            $role_id = $request->role_id;
            $payroll_month = $request->payroll_month;
            $payroll_year = $request->payroll_year;

            $query = '';
            if ($request->role_id !== '') {
                $query = sprintf("AND s.role_id = '%s'", $request->role_id);
            }

            if ($request->payroll_month !== '') {
                $query .= sprintf("AND pg.payroll_month = '%s'", $request->payroll_month);
            }

            if ($request->payroll_year !== '') {
                $query .= sprintf("AND pg.payroll_year = '%s'", $request->payroll_year);
            }

            $school_id = Auth::user()->school_id;

            $staffsPayroll = DB::query()->selectRaw(DB::raw("pg.*, s.full_name, r.name, d.title
												FROM sm_hr_payroll_generates pg
												LEFT JOIN sm_staffs s ON pg.staff_id = s.id
												LEFT JOIN roles r ON s.role_id = r.id
												LEFT JOIN sm_designations d ON s.designation_id = d.id
												WHERE pg.active_status =1 AND pg.school_id = '{$school_id}'
												{$query}"))->get();

            $roles = InfixRole::select(['id', 'name'])->where('is_saas', 0)->where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            return view('backEnd.reports.payroll', ['staffsPayroll' => $staffsPayroll, 'roles' => $roles, 'payroll_month' => $payroll_month, 'payroll_year' => $payroll_year, 'role_id' => $role_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewPayrollPayment($generate_id)
    {
        $generatePayroll = SmHrPayrollGenerate::find($generate_id);
        $payrollPayments = $generatePayroll->payrollPayments;

        return view('backEnd.humanResource.payroll.view_payroll_payment_modal', ['generatePayroll' => $generatePayroll, 'payrollPayments' => $payrollPayments]);
    }

    public function deletePayrollPayment(Request $request)
    {
        /*
        try {
        */
            $msg = 'Id Not Found';

            if ($request->ids) {
                foreach ($request->ids as $payroll_payment_id) {
                    $payrollPayment = PayrollPayment::find($payroll_payment_id);

                    if (auth()->user()->id == $payrollPayment->created_by || auth()->user()->role_id == 1) {
                        $expenseDetail = SmAddExpense::where('payroll_payment_id', $payroll_payment_id)->first();
                        if ($expenseDetail) {

                            $expenseDetail->delete();
                        }

                        $bankStatementDetail = SmBankStatement::where('payroll_payment_id', $payroll_payment_id)->first();
                        if ($bankStatementDetail) {
                            $bankStatementDetail->delete();
                        }

                        $generatePayroll = SmHrPayrollGenerate::find($payrollPayment->sm_hr_payroll_generate_id);
                        $generatePayroll->net_salary += $payrollPayment->amount;
                        $generatePayroll->save();
                        $payrollPayment->delete();
                    }
                }

                $msg = 'Operation Successfully';
            }

            return response()->json(['msg' => $msg]);
        /*
        } catch (Throwable $throwable) {
            return response()->json(['msg' => $throwable->getMessage()]);
        }
        */
    }

    public function printPayrollPayment($id)
    {
        /*
        try {
        */
            $payrollPayment = PayrollPayment::find($id);
            $schoolDetails = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $payrollDetails = SmHrPayrollGenerate::find($payrollPayment->sm_hr_payroll_generate_id);

            $payrollEarnDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'E')->where('school_id', Auth::user()->school_id)->get();

            $payrollDedcDetails = SmHrPayrollEarnDeduc::where('active_status', '=', '1')->where('payroll_generate_id', '=', $id)->where('earn_dedc_type', '=', 'D')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.humanResource.payroll.payment_payslip_print', ['payrollDetails' => $payrollDetails, 'payrollEarnDetails' => $payrollEarnDetails, 'payrollDedcDetails' => $payrollDedcDetails, 'schoolDetails' => $schoolDetails, 'payrollPayment' => $payrollPayment]);
        /*
        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
