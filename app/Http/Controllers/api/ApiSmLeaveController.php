<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAcademicYear;
use App\SmAssignClassTeacher;
use App\SmClassTeacher;
use App\SmLeaveDefine;
use App\SmLeaveRequest;
use App\SmNotification;
use App\SmStaff;
use App\SmStudent;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiSmLeaveController extends Controller
{
    public function myLeaveType(Request $request, $user_id)
    {
        try {
            $user = User::find($user_id);

            if ($user->role_id !== 3) {
                $leaves = DB::table('sm_leave_defines')
                    ->where('role_id', $user->role_id)
                    ->join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_defines.type_id')
                    ->where('sm_leave_defines.user_id', $user_id)
                    ->where('sm_leave_defines.academic_id', SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                    ->where('sm_leave_defines.school_id', $request->user()->school_id)
                    ->select('sm_leave_types.id', 'sm_leave_types.type', DB::raw('MAX(sm_leave_defines.days) as days'))
                    ->groupBy('sm_leave_types.id', 'sm_leave_types.type')
                    ->get();
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leaves, null);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }

        return null;
    }

    public function saas_myLeaveType(Request $request, $school_id, $user_id)
    {
        try {
            $user = User::find($user_id);

            if ($user->role_id !== 3) {

                $leaves = DB::table('sm_leave_defines')->where('role_id', $user->role_id)
                    ->join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_defines.type_id')
                    ->where('sm_leave_defines.user_id', $user_id)
                    ->where('sm_leave_defines.academic_id', SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                    ->where('sm_leave_defines.school_id', $request->user()->school_id)
                    ->select('sm_leave_types.id', 'sm_leave_types.type', 'sm_leave_defines.days')
                    ->get();
            } else {
                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leaves, null);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }

        return null;
    }

    public function studentleaveApply(Request $request, $user_id)
    {
        try {
            $user = User::find($user_id);
            $std_id = SmStudent::leftjoin('sm_parents', 'sm_parents.id', 'sm_students.parent_id')
                ->where('sm_parents.user_id', $user->id)
                ->where('sm_students.active_status', 1)
                ->where('sm_students.academic_id', SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                ->where('sm_students.school_id', $request->user()->school_id)
                ->select('sm_students.user_id')
                ->first();
            $my_leaves = SmLeaveDefine::join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_defines.type_id')
                ->where('sm_leave_defines.user_id', $user_id)
                ->where('sm_leave_defines.school_id', $request->user()->school_id)
                ->select(
                    'sm_leave_types.id',
                    'sm_leave_types.type',
                    DB::raw('SUM(sm_leave_defines.days) as days')
                )
                ->groupBy('sm_leave_types.id', 'sm_leave_types.type')
                ->get();

            $apply_leaves = SmLeaveRequest::where('staff_id', $user_id)
                ->where('role_id', 2)
                ->where('sm_leave_requests.approve_status', '=', 'P')
                ->where('sm_leave_requests.active_status', 1)
                ->join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_requests.type_id')
                ->where('sm_leave_requests.academic_id', SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id))
                ->where('sm_leave_requests.school_id', $request->user()->school_id)
                ->select('sm_leave_requests.id', 'sm_leave_types.type', 'sm_leave_requests.apply_date', 'sm_leave_requests.leave_from', 'sm_leave_requests.leave_to', 'sm_leave_requests.approve_status', 'sm_leave_requests.active_status')
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

        return null;
    }

    public function leaveStoreStudent(Request $request)
    {
        $user = User::find($request->login_id);

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'apply_date' => 'required',
                'leave_type' => 'nullable|exists:sm_leave_types,id',
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => 'required',
                'login_id' => 'required',
                'attach_file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors()->all());
        }

        try {

            $fileName = '';
            if ($request->file('attach_file') !== '') {
                $file = $request->file('attach_file');
                $fileName = $request->input('login_id').time().'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/leave_request/', $fileName);
                $fileName = 'public/uploads/leave_request/'.$fileName;
            }

            $smLeaveRequest = new SmLeaveRequest();
            $smLeaveRequest->staff_id = $request->login_id;
            $smLeaveRequest->role_id = $user->role_id;
            $smLeaveRequest->apply_date = date('Y-m-d', strtotime($request->apply_date));
            if ($request->leave_type) {
                $smLeaveRequest->leave_define_id = $request->leave_type;
            }

            $smLeaveRequest->type_id = $request->leave_type;
            $smLeaveRequest->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $smLeaveRequest->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $smLeaveRequest->approve_status = 'P';
            $smLeaveRequest->reason = $request->reason;
            $smLeaveRequest->file = $fileName;
            $smLeaveRequest->school_id = $request->user()->school_id;
            $smLeaveRequest->academic_id = SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id);
            $result = $smLeaveRequest->save();

            if ($user->role_id == 2) {
                $student = SmStudent::withoutGlobalScopes()->where('user_id', $request->login_id)->first();

                $teacher_assign = SmAssignClassTeacher::where('class_id', $student->class_id)->where('section_id', $student->section_id)->first();
                if ($teacher_assign) {
                    $classTeacher = SmClassTeacher::select('teacher_id')
                        ->where('assign_class_teacher_id', $teacher_assign->id)
                        ->first();

                    $notification = new SmNotification();
                    $notification->message = $student->full_name.'Apply For Leave';
                    $notification->is_read = 0;
                    $notification->url = 'pending-leave';
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = $request->user()->school_id;
                    $notification->academic_id = $student->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }

            if ($result) {
                $users = User::whereIn('role_id', [1, 5])->where('school_id', $request->user()->school_id)->get();
                foreach ($users as $user) {
                    $notification = new SmNotification();
                    $notification->message = $user->full_name.'Apply For Leave';
                    $notification->is_read = 0;
                    $notification->url = 'pending-leave';
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = 1;
                    $notification->academic_id = $user->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

        } catch (Exception $exception) {
            return $exception;
        }

        return null;
    }

    public function pendingLeave(Request $request, $user_id)
    {
        try {
            $user = User::select('id', 'role_id', 'school_id')->find($user_id);
            $staff = SmStaff::where('user_id', $user->id)->first();

            if ($user->role_id == 1 || $user->role_id == 5) {
                $pending_leaves = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                    ->where('sm_leave_requests.approve_status', '=', $request->purpose)
                    ->where('sm_leave_requests.school_id', '=', $request->user()->school_id)
                    ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                    ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                    ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
                    ->select('sm_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
                    ->get();
            } elseif ($user->role_id == 4) {
                $pending_leaves = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                    ->where('sm_leave_requests.approve_status', '=', $request->purpose)
                    ->where('sm_leave_requests.staff_id', '=', $user->id)
                    ->where('sm_leave_requests.school_id', '=', $request->user()->school_id)
                    ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                    ->join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_defines.type_id')
                    ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                    ->select('sm_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
                    ->get();
            } else {
                $pending_leaves = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                    ->where('sm_leave_requests.staff_id', '=', $user->id)
                    ->where('sm_leave_requests.approve_status', '=', $request->purpose)
                    ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                    ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                    ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
                    ->where('sm_leave_requests.school_id', $user->school_id)
                    ->where('sm_leave_requests.academic_id', SmAcademicYear::API_ACADEMIC_YEAR($user->school_id))
                    ->select('sm_leave_requests.id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
                    ->get();
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['pending_leaves'] = $pending_leaves->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

        return null;
    }

    public function leaveApprove(Request $request)
    {
        try {
            $input = $request->all();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $validator = Validator::make($input, [
                    'id' => 'required',
                    'user_id' => 'required',
                    'approve_status' => 'required',
                ]);
            }

            if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            $user = User::select('id', 'role_id')->find($request->user_id);
            if ($user->role_id == 1 || $user->role_id == 5) {
                $leave_request_data = SmLeaveRequest::find($request->id);
            } else {
                $leave_request_data = SmLeaveRequest::where('id', $request->id)->where('school_id', $request->user()->school_id)->first();
            }

            $staff_id = $leave_request_data->staff_id;
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->approve_status;
            $leave_request_data->academic_id = getAcademicId();
            $result = $leave_request_data->save();

            $smNotification = new SmNotification;
            $smNotification->user_id = $leave_request_data->staff_id;
            $smNotification->role_id = $role_id;
            $smNotification->date = date('Y-m-d');
            $smNotification->message = 'Leave status updated';
            $smNotification->school_id = $request->user()->school_id;
            $smNotification->academic_id = SmAcademicYear::API_ACADEMIC_YEAR($request->user()->school_id);
            $smNotification->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updates successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }
        } catch (Exception $exception) {

            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

        return null;
    }

    public function allPendingList(Request $request)
    {

        $pendingRequest = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
            ->select('sm_leave_requests.id', 'sm_leave_requests.staff_id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
            ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
            ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
            ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
            ->where('sm_leave_requests.approve_status', '=', 'P')
            ->where('sm_leave_requests.school_id', $request->user()->school_id)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['pending_request'] = $pendingRequest->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return null;
    }

    public function allAprroveList(Request $request)
    {
        $aprroveRequest = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
            ->select('sm_leave_requests.id', 'sm_leave_requests.staff_id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
            ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
            ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
            ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
            ->where('sm_leave_requests.school_id', $request->user()->school_id)
            ->where('sm_leave_requests.approve_status', '=', 'A')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['aprrove_request'] = $aprroveRequest->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return null;
    }

    public function allRejectedList(Request $request)
    {
        $rejectedRequest = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
            ->select('sm_leave_requests.id', 'sm_leave_requests.staff_id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
            ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
            ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
            ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
            ->where('sm_leave_requests.school_id', $request->user()->school_id)
            ->where('sm_leave_requests.approve_status', '=', 'C')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['rejected_request'] = $rejectedRequest->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return null;
    }

    public function rejectUserLeave(Request $request, $user_id)
    {
        $user = User::find($user_id);
        $rejectedRequest = [];
        if ($user) {
            $rejectedRequest = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                ->select('sm_leave_requests.id', 'sm_leave_requests.staff_id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
                ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
                ->where('sm_leave_requests.staff_id', '=', $user_id)
                ->where('sm_leave_requests.approve_status', '=', 'C')
                ->where('sm_leave_requests.school_id', $user->school_id)
                ->get();
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['rejected_request'] = $rejectedRequest->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return null;
    }

    public function userApproveLeave(Request $request, $user_id)
    {
        $aprroveRequest = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
            ->select('sm_leave_requests.id', 'sm_leave_requests.staff_id', 'users.full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'sm_leave_types.type', 'approve_status')
            ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
            ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
            ->leftjoin('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
            ->where('sm_leave_requests.staff_id', '=', $user_id)
            ->where('sm_leave_requests.approve_status', '=', 'A')
            ->where('sm_leave_requests.school_id', $request->user()->school_id)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['aprrove_request'] = $aprroveRequest->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return null;
    }

    public function rejectLeave(Request $request)
    {
        try {
            $reject_request = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                ->select('sm_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                ->join('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
                ->where('sm_leave_requests.approve_status', '=', 'R')
                ->where('sm_leave_requests.school_id', $request->user()->school_id)
                ->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['reject_request'] = $reject_request->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

        return null;
    }

    public function saas_rejectLeave(Request $request, $school_id)
    {
        try {
            $reject_request = SmLeaveRequest::where('sm_leave_requests.active_status', 1)
                ->select('sm_leave_requests.id', 'full_name', 'apply_date', 'leave_from', 'leave_to', 'reason', 'file', 'type', 'approve_status')
                ->join('sm_leave_defines', 'sm_leave_requests.leave_define_id', '=', 'sm_leave_defines.id')
                ->join('users', 'sm_leave_requests.staff_id', '=', 'users.id')
                ->join('sm_leave_types', 'sm_leave_requests.type_id', '=', 'sm_leave_types.id')
                ->where('sm_leave_requests.approve_status', '=', 'C')
                ->where('sm_leave_requests.school_id', $request->user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['reject_request'] = $reject_request->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

        return null;
    }
}
