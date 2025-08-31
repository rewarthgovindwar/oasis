<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Leave\SmLeaveTypeRequest;
use App\SmLeaveType;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmLeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
            $leave_types = SmLeaveType::select(['type', 'total_days', 'id'])->get();

            return view('backEnd.humanResource.leave_type', ['leave_types' => $leave_types]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmLeaveTypeRequest $smLeaveTypeRequest)
    {
        /*
        try {
        */
            $smLeaveType = new SmLeaveType();
            $smLeaveType->type = $smLeaveTypeRequest->type;
            $smLeaveType->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smLeaveType->un_academic_id = getAcademicId();
            } else {
                $smLeaveType->academic_id = getAcademicId();
            }

            $smLeaveType->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            if (checkAdmin() == true) {
                $leave_type = SmLeaveType::find($id);
            } else {
                $leave_type = SmLeaveType::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $leave_types = SmLeaveType::get();

            return view('backEnd.humanResource.leave_type', ['leave_types' => $leave_types, 'leave_type' => $leave_type]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmLeaveTypeRequest $smLeaveTypeRequest, $id)
    {
        /*
        try {
        */
            if (checkAdmin() == true) {
                $leave_type = SmLeaveType::find($smLeaveTypeRequest->id);
            } else {
                $leave_type = SmLeaveType::where('id', $smLeaveTypeRequest->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $leave_type->type = $smLeaveTypeRequest->type;
            $leave_type->total_days = $smLeaveTypeRequest->total_days;
            if (moduleStatusCheck('University')) {
                $leave_type->un_academic_id = getAcademicId();
            }

            $leave_type->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('leave-type');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('type_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    if (checkAdmin() == true) {
                        SmLeaveType::destroy($id);
                    } else {
                        SmLeaveType::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

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
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
