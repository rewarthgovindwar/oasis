<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeesCollection\SmFeesCollectSearchRequest;
use App\Models\DirectFeesInstallmentAssign;
use App\Models\FeesInvoice;
use App\Models\StudentRecord;
use App\SmClass;
use App\SmFeesAssign;
use App\SmFeesAssignDiscount;
use App\SmFeesPayment;
use App\SmGeneralSettings;
use App\SmSection;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\University\Entities\UnFeesInstallmentAssign;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmFeesCollectController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::get();

            return view('backEnd.feesCollection.collect_fees', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function search(SmFeesCollectSearchRequest $smFeesCollectSearchRequest)
    {
        /*
        try {
        */
            $data = [];
            $students = StudentRecord::query();
            if (moduleStatusCheck('University')) {
                $students = universityFilter($students, $smFeesCollectSearchRequest);
                $students = $students->with('studentDetail.parents')
                    ->whereHas('studentDetail', function ($q): void {
                        $q->where('active_status', 1);
                    })->get();
            } else {
                if ($smFeesCollectSearchRequest->class !== '') {
                    $students->where('class_id', $smFeesCollectSearchRequest->class);
                } else {
                    $students->whereNotNull('class_id');
                }

                if ($smFeesCollectSearchRequest->section !== '') {
                    $students->where('section_id', $smFeesCollectSearchRequest->section);
                } else {
                    $students->whereNotNull('section_id');
                }

                if ($smFeesCollectSearchRequest->keyword !== '') {
                    $students->whereHas('studentDetail', function ($q) use ($smFeesCollectSearchRequest): void {
                        $q->where('full_name', 'like', '%'.$smFeesCollectSearchRequest->keyword.'%')->orWhere('admission_no', $smFeesCollectSearchRequest->keyword)->orWhere('roll_no', $smFeesCollectSearchRequest->keyword)->orWhere('national_id_no', $smFeesCollectSearchRequest->keyword)->orWhere('local_id_no', $smFeesCollectSearchRequest->keyword);
                    });
                }

                $students = $students->with('class', 'section', 'studentDetail.parents')
                    ->whereHas('studentDetail', function ($q): void {
                        $q->where('active_status', 1);
                    })->get();
            }

            if ($students->isEmpty()) {
                if (ApiBaseMethod::checkUrl($smFeesCollectSearchRequest->fullUrl())) {
                    return ApiBaseMethod::sendError('No result found');
                }

                Toastr::error('No result found', 'Failed');

                return redirect('collect-fees');
            }

            $classes = SmClass::get();

            $class_info = SmClass::find($smFeesCollectSearchRequest->class);
            $search_info['class_name'] = @$class_info->class_name;
            if ($smFeesCollectSearchRequest->section !== '') {
                $section_info = SmSection::find($smFeesCollectSearchRequest->section);
                $search_info['section_name'] = @$section_info->section_name;
            }

            if ($smFeesCollectSearchRequest->keyword !== '') {
                $search_info['keyword'] = $smFeesCollectSearchRequest->keyword;
            }

            $data['classes'] = $classes;
            $data['students'] = $students;
            $data['search_info'] = $search_info;

            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($smFeesCollectSearchRequest);
            }

            return view('backEnd.feesCollection.collect_fees', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function collectFeesStudent(Request $request, $id)
    {
        /*
        try {
        */

            $student = StudentRecord::with('studentDetail', 'feesDiscounts', 'fees')->find($id);
            $fees_assigneds = SmFeesAssign::with('feesGroupMaster')
                ->where('student_id', $student->student_id)
                ->where('record_id', $id)
                ->orderBy('id', 'desc')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (count($fees_assigneds) <= 0) {
                Toastr::warning('Fees not assigned yet!');

                return redirect('/collect-fees');
            }

            $fees_discounts = $student->feesDiscounts;

            $applied_discount = [];
            foreach ($fees_discounts as $fee_discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')
                    ->where('active_status', 1)
                    ->where('record_id', $id)
                    ->where('fees_discount_id', $fee_discount->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();

                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            $data['student'] = $student;
            $data['invoice_settings'] = FeesInvoice::where('school_id', auth()->user()->school_id)->first(['prefix', 'start_form']);
            $data['fees_assigneds'] = $student->fees;
            $data['fees_discounts'] = $fees_discounts;
            $data['applied_discount'] = $applied_discount;
            if (moduleStatusCheck('University')) {
                $data['feesInstallments'] = UnFeesInstallmentAssign::where('un_academic_id', $student->un_academic_id)->where('un_semester_label_id', $student->un_semester_label_id)->where('record_id', $student->id)->get();
            } elseif (directFees()) {
                $data['feesInstallments'] = DirectFeesInstallmentAssign::where('academic_id', getAcademicId())->with('payments')->where('record_id', $student->id)->get();
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function collectFeesStudentApi(Request $request, $id)
    {
        /*
        try {
        */
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
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
