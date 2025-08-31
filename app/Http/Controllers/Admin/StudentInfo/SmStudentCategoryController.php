<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentInfo\SmStudentCategoryRequest;
use App\SmStudentCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmStudentCategoryController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $student_types = SmStudentCategory::get();

            return view('backEnd.studentInformation.student_category', ['student_types' => $student_types]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmStudentCategoryRequest $smStudentCategoryRequest)
    {
        /*
        try {
        */
            $smStudentCategory = new SmStudentCategory();
            $smStudentCategory->category_name = $smStudentCategoryRequest->category;
            $smStudentCategory->school_id = Auth::user()->school_id;
            $smStudentCategory->academic_id = getAcademicId();
            $smStudentCategory->save();
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
            $student_type = SmStudentCategory::find($id);
            $student_types = SmStudentCategory::get();

            return view('backEnd.studentInformation.student_category', ['student_types' => $student_types, 'student_type' => $student_type]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmStudentCategoryRequest $smStudentCategoryRequest)
    {
        /*
        try {
        */
            $student_type = SmStudentCategory::find($smStudentCategoryRequest->id);
            $student_type->category_name = $smStudentCategoryRequest->category;
            $student_type->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('student-category');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('student_category_id', $id);
            /*
            try {
            */
                if (!$tables) {
                    SmStudentCategory::find($id)->delete();
                } else {
                    $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                    Toastr::error($msg, 'Failed');

                    return redirect()->back();
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            /*
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            } catch (Exception $e) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
