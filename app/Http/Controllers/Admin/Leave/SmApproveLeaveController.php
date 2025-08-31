<?php

namespace App\Http\Controllers\Admin\Leave;

use App\User;
use Exception;
use App\SmStaff;
use App\SmParent;
use App\SmLeaveType;
use App\SmLeaveDefine;
use App\SmLeaveRequest;
use App\SmNotification;
use Illuminate\Http\Request;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\RolePermission\Entities\InfixRole;
use App\Http\Requests\Admin\Leave\SmApproveLeaveRequest;
use App\Http\Controllers\Admin\SystemSettings\SmSystemSettingController;

class SmApproveLeaveController extends Controller
{
    use NotificationSend;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        /*
        try {
        */
            $user = Auth::user();
            $staff = SmStaff::where('user_id', Auth::user()->id)->first();
            if (Auth::user()->role_id == 1) {
                $apply_leaves = SmLeaveRequest::with('leaveDefine', 'staffs', 'student')->where([['active_status', 1], ['approve_status', '!=', 'P']])->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->get();
            } else {
                $apply_leaves = SmLeaveRequest::with('leaveDefine', 'staffs', 'student')->where([['active_status', 1], ['approve_status', '!=', 'P'], ['staff_id', '=', $staff->id]])->where('academic_id', getAcademicId())->get();
            }

            $leave_types = SmLeaveType::where('active_status', 1)->get();
            $roles = InfixRole::where('is_saas', 0)->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            
            return view('backEnd.humanResource.approveLeaveRequest', ['apply_leaves' => $apply_leaves, 'leave_types' => $leave_types, 'roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function pendingLeave(Request $request)
    {
        /*
        try {
        */
            $user = Auth::user();
            $staff = SmStaff::where('user_id', Auth::user()->id)->first();

            $leave_types = SmLeaveType::where('active_status', 1)->get();
            $roles = InfixRole::where('is_saas', 0)->where('id', '!=', 1)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            return view('backEnd.humanResource.approveLeaveRequest', ['leave_types' => $leave_types, 'roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmApproveLeaveRequest $smApproveLeaveRequest)
    {
        /*
        try {
        */
            $path = 'public/uploads/leave_request/';
            $fileName = fileUpload($smApproveLeaveRequest->attach_file, $path);
            $user = Auth()->user();

            if ($user) {
                $login_id = $user->id;
                $role_id = $user->role_id;
            } else {
                $login_id = $smApproveLeaveRequest->login_id;
                $role_id = $smApproveLeaveRequest->role_id;
            }

            $smLeaveRequest = new SmLeaveRequest();
            $smLeaveRequest->staff_id = $login_id;
            $smLeaveRequest->role_id = $role_id;
            $smLeaveRequest->apply_date = date('Y-m-d', strtotime($smApproveLeaveRequest->apply_date));
            $smLeaveRequest->type_id = $smApproveLeaveRequest->leave_type;
            $smLeaveRequest->leave_from = date('Y-m-d', strtotime($smApproveLeaveRequest->leave_from));
            $smLeaveRequest->leave_to = date('Y-m-d', strtotime($smApproveLeaveRequest->leave_to));
            $smLeaveRequest->approve_status = $smApproveLeaveRequest->approve_status;
            $smLeaveRequest->reason = $smApproveLeaveRequest->reason;
            $smLeaveRequest->file = $fileName;
            $smLeaveRequest->school_id = Auth::user()->school_id;
            $smLeaveRequest->save();

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
            if (checkAdmin() == true) {
                $editData = SmLeaveRequest::find($id);
            } else {
                $editData = SmLeaveRequest::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $staffsByRole = SmStaff::where('role_id', '=', $editData->role_id)->where('school_id', Auth::user()->school_id)->get();
            $roles = InfixRole::where('is_saas', 0)->whereOr(['school_id', Auth::user()->school_id], ['school_id', 1])->get();
            $apply_leaves = SmLeaveRequest::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $leave_types = SmLeaveType::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.humanResource.approveLeaveRequest', ['editData' => $editData, 'staffsByRole' => $staffsByRole, 'apply_leaves' => $apply_leaves, 'leave_types' => $leave_types, 'roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy($id)
    {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function staffNameByRole(Request $request)
    {
        /*
        try {
        */
            if ($request->id != 3) {
                $allStaffs = SmStaff::whereRole($request->id)->where('school_id', Auth::user()->school_id)->get(['id', 'full_name', 'user_id']);
                $staffs = [];
                foreach ($allStaffs as $allStaff) {
                    $staffs[] = SmStaff::where('id', $allStaff->id)->first(['id', 'full_name', 'user_id']);
                }
            } else {
                $staffs = SmParent::where('active_status', 1)->get(['id', 'fathers_name', 'guardians_name', 'user_id']);
            }

            return response()->json([$staffs]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateApproveLeave(Request $request)
    {

        /*
        try {
        */
            if (checkAdmin() == true) {
                $leave_request_data = SmLeaveRequest::find($request->id);
            } else {
                $leave_request_data = SmLeaveRequest::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $staff = User::find($leave_request_data->staff_id);
            $role_id = $leave_request_data->role_id;
            $leave_request_data->approve_status = $request->approve_status;
            $leave_request_data->academic_id = getAcademicId();
            $result = $leave_request_data->save();

            $status = '';
            if ($request->approve_status == 'A') {
                $status = 'Leave_Approved';
            } elseif ($request->approve_status == 'C') {
                $status = 'Leave_Declined';
            } else {
                Toastr::success('Operation successful', 'Success');
                return redirect('approve-leave');
            }

            $data['to_date'] = $leave_request_data->leave_to;
            $data['name'] = $leave_request_data->user->full_name;
            $data['from_date'] = $leave_request_data->leave_from;
            $data['teacher_name'] = $leave_request_data->user->full_name;
            if ($leave_request_data->role_id == 2) {
                $this->sent_notifications($status, (array) $leave_request_data->user->id, $data, ['Student', 'Parent']);
            }

            if ($leave_request_data->role_id == 4) {
                $this->sent_notifications($status, (array) $leave_request_data->user->id, $data, ['Teacher']);
            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('approve-leave');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewLeaveDetails(Request $request, $id)
    {
        /*
        try {
        */

            if (checkAdmin() == true) {
                $leaveDetails = SmLeaveRequest::find($id);
            } else {
                $leaveDetails = SmLeaveRequest::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }
            $staff_leaves = SmLeaveDefine::where('user_id', $leaveDetails->staff_id)->where('role_id', $leaveDetails->role_id)->get();
            return view('backEnd.humanResource.viewLeaveDetails', ['leaveDetails' => $leaveDetails, 'staff_leaves' => $staff_leaves]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    protected function sendFcmNotifications($user, $messageKey)
    {
        if ($user) {

            $title = '';
            $desc = '';
            if ($messageKey == 'Leave_Approved') {
                $desc = 'Leave Has Been Approved';
                $title = 'Leave Approved';
            } elseif ($messageKey == 'Leave_Declined') {
                $desc = 'Leave Has Been Declined';
                $title = 'Leave Rejected';
            }

            $smNotification = new SmNotification;
            $smNotification->user_id = $user->id;
            $smNotification->role_id = 2;
            $smNotification->date = date('Y-m-d');
            $smNotification->message = $desc;
            $smNotification->school_id = Auth::user()->school_id;
            $smNotification->academic_id = getAcademicId();
            $smNotification->save();

            try {
                $user = User::find($user->id);
                if ($user) {
                    $notificationData = [
                        'id' => $user->id,
                        'title' => $title,
                        'body' => $smNotification->message,
                    ];
                    $smSystemSettingController = new SmSystemSettingController();
                    $smSystemSettingController->flutterNotificationApi(new Request($notificationData));
                }
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
