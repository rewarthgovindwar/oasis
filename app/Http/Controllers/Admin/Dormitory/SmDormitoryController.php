<?php

namespace App\Http\Controllers\Admin\Dormitory;

use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\SmClass;
use App\SmDormitoryList;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmDormitoryController extends Controller
{
    public function studentDormitoryReport(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::get();
            $dormitories = SmDormitoryList::get();
            $students = SmStudent::with('class', 'section', 'parents', 'dormitory', 'room')
                ->whereNotNull('dormitory_id')->get();

            return view('backEnd.dormitory.student_dormitory_report', ['classes' => $classes, 'students' => $students, 'dormitories' => $dormitories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function studentDormitoryReportSearch(Request $request)
    {
        /*
        try {
        */  
            $data = [];
            $stdent_ids = [];
            $students = SmStudent::query();
            $student_records = StudentRecord::query();
            if (moduleStatusCheck('University')) {
                $student_ids = universityFilter($student_records, $request)
                    ->distinct('student_id')->get('student_id');
                foreach ($student_ids as $student_id) {
                    $stdent_ids[] = $student_id->student_id;
                }
            } else {
                $student_ids = SmStudentReportController::classSectionStudent($request);
            }

            if ($request->dormitory !== '') {
                $students->where('dormitory_id', $request->dormitory);
            } else {
                $students->where('dormitory_id', '!=', '');
            }

            $students = $students->whereIn('id', $student_ids)->with('class', 'section', 'parents', 'dormitory', 'room')->where('school_id', Auth::user()->school_id)->get();

            $data['classes'] = SmClass::get();
            $data['dormitories'] = SmDormitoryList::get();
            $data['students'] = $students;
            $data['shift_id'] = $request->shift;
            $data['class_id'] = $request->class;
            $data['section_id'] = $request->section;
            $data['dormitory_id'] = $request->dormitory;
            if (moduleStatusCheck('University')) {
                $interface = App::make(UnCommonRepositoryInterface::class);
                $data += $interface->getCommonData($request);
            }

            return view('backEnd.dormitory.student_dormitory_report', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
