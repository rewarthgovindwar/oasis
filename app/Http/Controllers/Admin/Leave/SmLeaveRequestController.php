<?php

namespace App\Http\Controllers\Admin\Leave;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Leave\SmLeaveRequest as FormRequest;
use App\Notifications\LeaveApprovedNotification;
use App\SmLeaveDefine;
use App\SmLeaveRequest;
use App\SmNotification;
use App\SmStaff;
use App\tableList;
use App\Traits\NotificationSend;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SmLeaveRequestController extends Controller
{
    use NotificationSend;



    public function index(Request $request)
    {

        /*
        try {
        */
            $user = Auth::user();
            if ($user) {
                $my_leaves = SmLeaveDefine::with(['leaveType' => function ($query): void {
                    $query->select(['type', 'id']);
                }])
                    ->where('user_id', $user->id)
                    ->where('role_id', $user->role_id)
                    ->where('school_id', $user->school_id)
                    ->select(['id', 'days', 'type_id','active_status'])
                    ->get();
                $apply_leaves = SmLeaveRequest::with(['leaveDefine' => function ($query): void {
                    $query->select(['type_id', 'id']);
                },
                    'leaveDefine.leaveType' => function ($query): void {
                        $query->select(['type', 'id']);
                    },
                ])
                    ->where('role_id', $user->role_id)
                    ->where('active_status', 1)
                    ->where('school_id', $user->school_id)
                    ->has('leaveDefine')
                    ->where('staff_id', $user->id)
                    ->select(['id', 'apply_date', 'leave_to', 'type_id', 'approve_status', 'leave_from'])
                    ->get();

                $leave_types = $my_leaves->where('active_status', 1);
            }

            return view('backEnd.humanResource.apply_leave', ['apply_leaves' => $apply_leaves, 'leave_types' => $leave_types, 'my_leaves' => $my_leaves]);
        /*
        } catch (Exception $exception) {
            dd($exception);
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(FormRequest $formRequest)
    {
        /*
        try {
        */
            $leaveDefine = SmLeaveDefine::where('id', $formRequest->leave_define_id)->first();
            $path = 'public/uploads/leave_request/';
            $smLeaveRequest = new SmLeaveRequest();
            $smLeaveRequest->staff_id = auth()->user()->id;
            $smLeaveRequest->role_id = auth()->user()->role_id;
            $smLeaveRequest->apply_date = date('Y-m-d', strtotime($formRequest->apply_date));
            $smLeaveRequest->leave_define_id = $formRequest->leave_define_id;
            $smLeaveRequest->type_id = $leaveDefine->type_id;
            $smLeaveRequest->leave_from = date('Y-m-d', strtotime($formRequest->leave_from));
            $smLeaveRequest->leave_to = date('Y-m-d', strtotime($formRequest->leave_to));
            $smLeaveRequest->approve_status = 'P';
            $smLeaveRequest->reason = $formRequest->reason;
            if ($formRequest->file('attach_file') !== '') {
                $smLeaveRequest->file = fileUpload($formRequest->attach_file, $path);
            }

            $smLeaveRequest->school_id = auth()->user()->school_id;
            $smLeaveRequest->academic_id = getAcademicId();
            $smLeaveRequest->save();

            $data['to_date'] = $smLeaveRequest->leave_to;
            $data['name'] = $smLeaveRequest->user->full_name;
            $data['from_date'] = $smLeaveRequest->leave_from;
            $data['teacher_name'] = $smLeaveRequest->user->full_name;
            $this->sent_notifications('Leave_Apply', (array) $smLeaveRequest->user->id, $data, ['Teacher']);

            $staffInfo = SmStaff::where('user_id', auth()->user()->id)->first();
            $compact['slug'] = 'staff';
            $compact['user_email'] = auth()->user()->email;
            $compact['staff_name'] = auth()->user()->full_name;
            @send_sms($staffInfo->mobile, 'staff_leave_appllication', $compact);

            try {
                $user = User::where('role_id', 1)->first();
                $smNotification = new SmNotification;
                $smNotification->user_id = $user->id;
                $smNotification->role_id = $user->role_id;
                $smNotification->date = date('Y-m-d');
                $smNotification->message = app('translator')->get('leave.leave_request');
                $smNotification->school_id = Auth::user()->school_id;
                $smNotification->academic_id = getAcademicId();
                $smNotification->save();
                Notification::send($user, new LeaveApprovedNotification($smNotification));
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {

            Toastr::error($exception->getMessage(), 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {


        /*
        try {
        */
            $user = Auth::user();
            if ($user) {
                $my_leaves = SmLeaveDefine::where('user_id', $user->id)->where('role_id', $user->role_id)->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = SmLeaveRequest::with('leaveDefine')->where('role_id', $user->role_id)->where('active_status', 1)
                    ->where('school_id', Auth::user()->school_id)->has('leaveDefine')->where('staff_id', Auth::user()->id)->get();
                $leave_types = SmLeaveDefine::where('role_id', $user->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            }

            $apply_leave = SmLeaveRequest::find($id);

            return view('backEnd.humanResource.apply_leave', ['apply_leave' => $apply_leave, 'apply_leaves' => $apply_leaves, 'leave_types' => $leave_types, 'my_leaves' => $my_leaves]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(FormRequest $formRequest)
    {
        /*
        try {
        */
            $path = 'public/uploads/leave_request/';
            $apply_leave = SmLeaveRequest::find($formRequest->id);
            $apply_leave->apply_date = date('Y-m-d', strtotime($formRequest->apply_date));
            $apply_leave->leave_from = date('Y-m-d', strtotime($formRequest->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($formRequest->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $formRequest->reason;
            if ($formRequest->file !== '') {
                $apply_leave->file = fileUpdate($apply_leave->file, $formRequest->file, $path);
            }

            $apply_leave->save();
            Toastr::success('Operation successful', 'Success');

            return redirect('apply-leave');
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
            $leaveDetails = SmLeaveRequest::find($id);
            $apply = '';
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['apply'] = $apply;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.viewLeaveDetails', ['leaveDetails' => $leaveDetails, 'apply' => $apply]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {

        $tables = tableList::getTableList('leave_request_id', $id);

        /*
        try {
        */
            if ($tables == null) {
                $apply_leave = SmLeaveRequest::find($id);

                if ($apply_leave->file !== '' && file_exists($apply_leave->file)) {
                    unlink($apply_leave->file);
                }

                $apply_leave->delete();

                Toastr::success('Operation successful', 'Success');
                if (Auth::user()->role_id == 1) {
                    return redirect('pending-leave');
                }

                return redirect('apply-leave');

            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteLeave(Request $request)
    {
        $id = $request->id;
        $tables = tableList::getTableList('leave_request_id', $id);

        /*
        try {
        */
            if ($tables == null) {
                $apply_leave = SmLeaveRequest::find($id);

                if ($apply_leave->file !== '' && file_exists($apply_leave->file)) {
                    unlink($apply_leave->file);
                }

                $apply_leave->delete();

                Toastr::success('Operation successful', 'Success');
                if (Auth::user()->role_id == 1) {
                    return redirect('pending-leave');
                }

                return redirect('apply-leave');

            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        */
    }
}
