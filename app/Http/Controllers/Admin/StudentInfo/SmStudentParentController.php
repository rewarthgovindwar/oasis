<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\Http\Controllers\Controller;
use App\SmClass;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class SmStudentParentController extends Controller
{
    public function parentList()
    {
        /*
        try {
        */
            $classes = SmClass::get();
            $parents = SmStudent::with('parents', 'studentRecord.class', 'studentRecord.section')->get();

            return view('backEnd.studentInformation.student_parent_list', ['parents' => $parents, 'classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function parentListSearch(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::get();
            $parents = SmStudent::with('parents', 'studentRecord.class', 'studentRecord.section')
                ->when($request->class_id, function ($q) use ($request): void {
                    $q->whereHas('studentRecord.class', function ($query) use ($request): void {
                        $query->where('class_id', $request->class_id);
                    });
                })
                ->when($request->section_id, function ($q) use ($request): void {
                    $q->whereHas('studentRecord.section', function ($query) use ($request): void {
                        $query->where('section_id', $request->section_id);
                    });
                })
                ->when($request->parent_name, function ($q) use ($request): void {
                    $q->whereHas('parents', function ($query) use ($request): void {
                        $query->where('guardians_name', 'like', '%'.$request->parent_name.'%')
                            ->orWhere('fathers_name', 'like', '%'.$request->parent_name.'%');
                    });
                })
                ->when($request->student_name, function ($q) use ($request): void {
                    $q->where('full_name', 'like', '%'.$request->student_name.'%');
                })->get();

            return view('backEnd.studentInformation.student_parent_list', ['parents' => $parents, 'classes' => $classes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
