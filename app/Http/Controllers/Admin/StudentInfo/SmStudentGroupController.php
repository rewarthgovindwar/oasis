<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentInfo\StudentGroupRequest;
use App\SmStudentGroup;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmStudentGroupController extends Controller
{
    public function index(Request $request)
    {

        /*
        try {
        */
        $student_groups = SmStudentGroup::withCount('students')
            ->where('school_id', Auth::user()->school_id)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($student_groups, null);
        }

        return view('backEnd.studentInformation.student_group', ['student_groups' => $student_groups]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function store(StudentGroupRequest $studentGroupRequest)
    {
        /*
        try {
        */
        $smStudentGroup = new SmStudentGroup();
        $smStudentGroup->group = $studentGroupRequest->group;
        $smStudentGroup->school_id = Auth::user()->school_id;
        $smStudentGroup->created_by = auth()->user()->id;
        $smStudentGroup->academic_id = getAcademicId();
        $result = $smStudentGroup->save();

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
        $student_group = SmStudentGroup::find($id);
        $student_groups = SmStudentGroup::withCount('students')->where('school_id', Auth::user()->school_id)->get();

        return view('backEnd.studentInformation.student_group', ['student_groups' => $student_groups, 'student_group' => $student_group]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(StudentGroupRequest $studentGroupRequest)
    {
        /*
        try {
        */
        $student_group = SmStudentGroup::find($studentGroupRequest->id);
        $student_group->group = $studentGroupRequest->group;
        $student_group->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('student-group');
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
        $tables = \App\tableList::getTableList('student_group_id', $id);
        /*
        try {
        */
        if (! $tables) {
            SmStudentGroup::destroy($id);
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        }
        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

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
