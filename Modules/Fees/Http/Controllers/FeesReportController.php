<?php
namespace Modules\Fees\Http\Controllers;
use Exception;
use DataTables;
use App\SmClass;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Modules\Fees\Entities\FmFeesInvoice;
class FeesReportController extends Controller
{
    public function dueFeesView()
    {
        try {
            $data = $this->allClass();
            return view('fees::report.feesDue', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function dueFeesSearch(Request $request)
    {
        try {
            $data = $this->feesSearch($request->merge(['due' => true]));
            return view('fees::report.feesDue', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function fineReportView()
    {
        try {
            $data = $this->allClass();
            return view('fees::report.fine', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function fineReportSearch(Request $request)
    {
        try {
            $data = $this->feesSearch($request->merge(['all' => true]));
            return view('fees::report.fine', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function paymentReportView()
    {
        try {
            $data = $this->allClass();
            return view('fees::report.payment', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function paymentReportSearch(Request $request)
    {
        try {
            $data = $this->feesSearch($request->merge(['all' => true]));
            return view('fees::report.payment', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function balanceReportView(Request $request)
    {
        try {
            $data = $this->allClass();
            return view('fees::report.balance', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function balanceReportSearch(Request $request)
    {
        try {
            $data = $this->feesSearch($request->merge(['all' => true]));
            return view('fees::report.balance', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function waiverReportView(Request $request)
    {
        try {
            $data = $this->allClass();
            return view('fees::report.waiver', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function waiverReportSearch(Request $request)
    {
        try {
            $data = $this->feesSearch($request->merge(['all' => true]));
            return view('fees::report.waiver', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    
    private function allClass()
    {
        $data['classes'] = SmClass::where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->select(['id', 'class_name'])
            ->get();
        return $data;
    }
    
    private function feesSearch($request): array
    {
        $data = [];
        $data['classes'] = SmClass::where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();
        if ($request->date_range) {
            $rangeArr = $request->date_range ? explode('-', $request->date_range) : [date('m/d/Y'), date('m/d/Y')];
            $date_from = date('Y-m-d', strtotime(trim($rangeArr[0])));
            $date_to = date('Y-m-d', strtotime(trim($rangeArr[1])));
        }
        $data['date_range'] = $request->date_range;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['class'] = $request->class;
        $data['section'] = $request->section;
        $data['shift'] = shiftEnable() ? $request->shift : '';
        $data['student'] = $request->student;
        return $data;
    }

    public function feesReportDatatable(Request $request)
    {
        try {
             // Extract common variables outside of the query to reduce redundancy
                $schoolId = auth()->user()->school_id;
                $academicId = getAcademicId();
                $classId = $request->class;
                $sectionId = $request->section;
                $shiftId = shiftEnable() ? $request->shift : '';
                $studentId = $request->student;
                $dateFrom = $request->date_from ? date('Y-m-d', strtotime($request->date_from)) : null;
                $dateTo = $request->date_to ? date('Y-m-d', strtotime($request->date_to)) : null;
                // Initialize the query
                
                
                $fees_dues = FmFeesInvoice::with(['studentInfo', 'recordDetail', 'invoiceDetails' => function ($query): void {
                    $query->selectRaw('fees_invoice_id, sum(amount) as total_amount, sum(weaver) as total_weaver, sum(fine) as total_fine, sum(paid_amount) as total_paid_amount, sum(sub_total) as total_sub_total')
                        ->groupBy('fees_invoice_id');
                }])->where('school_id', $schoolId)
                    ->where('academic_id', $academicId)
                    ->select(['id', 'due_date', 'student_id', 'class_id', 'record_id', 'created_at']);
                    
                
                // Apply conditional filters
                if (!empty($classId)) {
                    $fees_dues->where('class_id', $classId);
                }
                // Combine sectionId and studentId conditions in whereHas() to avoid duplicate queries
                if (!empty($sectionId)) {
                    $fees_dues->whereHas('recordDetail', function ($query) use ($sectionId): void {
                        if ($sectionId) {
                            $query->where('section_id', $sectionId);
                        }
                    });
                }

                if (!empty($shiftId)) {
                    $fees_dues->whereHas('recordDetail', function ($query) use ($shiftId): void {
                        if ($shiftId) {
                            $query->where('shift_id', $shiftId);
                        }
                    });
                }

                if (!empty($studentId)) {
                    $fees_dues->where('student_id', $studentId);
                }

                // Apply date range filtering if both dateFrom and dateTo are provided
                if ($dateFrom && $dateTo) {
                    $fees_dues->whereDate('created_at', '>=', $dateFrom)
                        ->whereDate('created_at', '<=', $dateTo);
                }
                // ->whereBetween('due_date', [$dateFrom, $dateTo])
                        
                // Return the data as Datatables response
                if($request->type == 'feesDue')
                {
                    $filtered = $fees_dues->get()->filter(function ($row) {
                        return ($row->Tsubtotal - $row->Tpaidamount + $row->Tfine) > 0;
                    });
                }elseif($request->type == 'payment'){
                    $filtered = $fees_dues->get()->filter(function ($row) {
                        return ($row->Tpaidamount) > 0;
                    });
                }elseif($request->type == 'fine'){
                    $filtered = $fees_dues->get()->filter(function ($row) {
                        return ($row->Tfine) > 0;
                    });
                }else{
                    $filtered = $fees_dues->get()->filter(function ($row) {
                        return ($row->Tweaver) > 0;
                    });
                }
                
                return DataTables::of($filtered)
                    ->addIndexColumn()
                    ->addColumn('due_date', function ($row) {
                        return dateConvert($row->due_date);
                    })
                    ->addColumn('fine', function ($row) {
                        return $row->Tfine;
                    })
                    ->addColumn('balance', function ($row) {
                        return $row->Tsubtotal - $row->Tpaidamount + $row->Tfine;
                    })
                    ->addColumn('amount', function ($row) {
                        return $row->Tamount;
                    })
                    ->addColumn('paid_amount', function ($row) {
                        return $row->Tpaidamount;
                    })
                    ->addColumn('weaver', function ($row) {
                        return $row->Tweaver;
                    })
                    ->addColumn('full_name', function ($row) {
                        return $row->studentInfo->full_name ?? 'N/A';
                    })
                    ->addColumn('roll_no', function ($row) {
                        return $row->recordDetail->roll_no ?? 'N/A';
                    })
                    ->rawColumns(['action', 'date'])
                    ->make(true);
            // if ($request->ajax()) {
               

            // }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        return null;
    }
}