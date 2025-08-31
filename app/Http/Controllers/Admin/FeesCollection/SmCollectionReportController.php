<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Models\DireFeesInstallmentChildPayment;
use App\Models\StudentRecord;
use App\SmClass;
use App\SmFeesPayment;
use Brian2694\Toastr\Facades\Toastr;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnFeesInstallmentAssign;

class SmCollectionReportController extends Controller
{
    public function transactionReport(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::select(['id', 'class_name', 'active_status', 'pass_mark'])->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, null);
            }

            return view('backEnd.feesCollection.transaction_report', ['classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function transactionReportSearch(Request $request)
    {
        $rangeArr = $request->date_range ? explode('-', $request->date_range) : ''.date('m/d/Y').' - '.date('m/d/Y').'';
        $date_from = null;
        $date_to = null;
        if ($request->date_range) {
            $date_from = new DateTimeImmutable(trim($rangeArr[0]));
            $date_to = new DateTimeImmutable(trim($rangeArr[1]));
        }

        $classes = [];
        /*
        try {
        */
            if (moduleStatusCheck('University')) {
                $StudentRecord = StudentRecord::query();
                $students = universityFilter($StudentRecord, $request)->get();

                $fees_payments = UnFeesInstallmentAssign::with('payments')->whereIn('active_status', [1, 2])
                    ->whereIn('student_id', $students->pluck('student_id'))
                    ->where('un_semester_label_id', $request->un_semester_label_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->when($request->date_range, function ($q) use ($date_from, $date_to): void {
                        $q->where('payment_date', '>=', $date_from);
                        $q->where('payment_date', '<=', $date_to);
                    })
                    ->where('paid_amount', '>', 0)
                    ->get();
            } elseif (directFees()) {
                $classes = SmClass::get();
                $allStudent = StudentRecord::when($request->class, function ($q) use ($request): void {
                    $q->where('class_id', $request->class);
                })
                    ->when($request->section, function ($q) use ($request): void {
                        $q->where('section_id', $request->section);
                    })
                    ->where('academic_id', getAcademicId())
                    ->get();
                $fees_payments = DireFeesInstallmentChildPayment::with('installmentAssign.recordDetail.studentDetail', 'installmentAssign.installment')->where('active_status', 1)
                    ->whereIn('record_id', $allStudent->pluck('id'))
                    ->when($request->date_range, function ($q) use ($date_from, $date_to): void {
                        $q->where('payment_date', '>=', $date_from);
                        $q->where('payment_date', '<=', $date_to);
                    })
                    ->where('paid_amount', '>', 0)
                    ->where('school_id', auth()->user()->school_id)
                    ->get();
            } else {
                $classes = SmClass::get();
                if ($request->date_range) {
                    if ($request->class) {
                        $students = StudentRecord::where('class_id', $request->class)
                            ->get();

                        $fees_payments = SmFeesPayment::where('active_status', 1)
                            ->whereIn('student_id', $students->pluck('student_id'))
                            ->where('payment_date', '>=', $date_from)
                            ->where('payment_date', '<=', $date_to)
                            ->where('school_id', Auth::user()->school_id)
                            ->get();
                        $fees_payments = $fees_payments->groupBy('student_id');
                    } else {
                        $fees_payments = SmFeesPayment::where('active_status', 1)
                            ->where('payment_date', '>=', $date_from)
                            ->where('payment_date', '<=', $date_to)
                            ->where('school_id', Auth::user()->school_id)
                            ->get();
                        $fees_payments = $fees_payments->groupBy('student_id');
                    }
                }

                if ($request->class && $request->section) {
                    $students = StudentRecord::where('class_id', $request->class)
                        ->where('section_id', $request->section)
                        ->where('school_id', Auth::user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->get();

                    $fees_payments = SmFeesPayment::where('active_status', 1)
                        ->whereIn('student_id', $students->pluck('student_id'))
                        ->where('payment_date', '>=', $date_from)
                        ->where('payment_date', '<=', $date_to)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();
                    $fees_payments = $fees_payments->groupBy('student_id');

                }

            }

            if (moduleStatusCheck('University')) {
                // $data = $this->unCommonRepository->oldValueSelected($request);
                return view('backEnd.feesCollection.transaction_report', ['fees_payments' => $fees_payments, 'date_to' => $date_to, 'date_from' => $date_from]);
            }

            if (directFees()) {
                // $data = $this->unCommonRepository->oldValueSelected($request);
                return view('backEnd.feesCollection.transaction_report', ['fees_payments' => $fees_payments, 'date_to' => $date_to, 'date_from' => $date_from, 'classes' => $classes]);
            }

            return view('backEnd.feesCollection.transaction_report', ['fees_payments' => $fees_payments, 'classes' => $classes, 'date_to' => $date_to, 'date_from' => $date_from]);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
