<?php

namespace App\Http\Controllers\api\v2\Teacher\Leave;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\Teacher\Leave\AppliedLeveListResource;
use App\Notifications\LeaveApprovedNotification;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\ActiveStatusSchoolScope;
use App\SmAcademicYear;
use App\SmLeaveDefine;
use App\SmLeaveRequest;
use App\SmNotification;
use App\SmStaff;
use App\Traits\NotificationSend;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LeaveController extends Controller
{
    use NotificationSend;

    public function list()
    {
        $user = auth()->user();

        if ($user) {
            $pending = SmLeaveRequest::with(['leaveType' => function ($q): void {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
                ->where('approve_status', 'P')
                ->where('staff_id', $user->id)
                ->where('role_id', $user->role_id)
                ->where('active_status', 1)
                ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('id', 'DESC')
                ->get();

            $approved = SmLeaveRequest::with(['leaveType' => function ($q): void {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
                ->where('approve_status', 'A')
                ->where('staff_id', $user->id)
                ->where('role_id', $user->role_id)
                ->where('active_status', 1)
                ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('id', 'DESC')
                ->get();

            $rejected = SmLeaveRequest::with(['leaveType' => function ($q): void {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
                ->where('approve_status', 'C')
                ->where('staff_id', $user->id)
                ->where('role_id', $user->role_id)
                ->where('active_status', 1)
                ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
                ->where('school_id', auth()->user()->school_id)
                ->orderBy('id', 'DESC')
                ->get();
        }

        $data['pending'] = AppliedLeveListResource::collection($pending);
        $data['approved'] = AppliedLeveListResource::collection($approved);
        $data['rejected'] = AppliedLeveListResource::collection($rejected);

        if ($data == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Leave list',
            ];
        }

        return response()->json($response);
    }

    public function types()
    {
        $user = auth()->user();
        if ($user) {
            $my_leaves = SmLeaveDefine::withoutGlobalScope(ActiveStatusSchoolScope::class)->with(['leaveType' => function ($q): void {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
                ->where('user_id', $user->id)
                ->where('role_id', $user->role_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('active_status', 1)
                ->get()
                ->map(function ($type): array {
                    return [
                        'id' => $type->id,
                        'type' => @$type->leaveType->type,
                    ];
                });
        }

        $response = [
            'success' => true,
            'data' => $my_leaves,
            'messege' => 'Operation Successfull.',
        ];

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'type_id' => 'required',
        ]);

        $leaveDefine = SmLeaveDefine::withoutGlobalScope(ActiveStatusSchoolScope::class)
            ->where('id', $request->type_id)
            ->where('school_id', auth()->user()->school_id)
            ->where('user_id', auth()->id())
            ->where('role_id', auth()->user()->role_id)
            ->where('active_status', 1)->first();
        $path = 'public/uploads/leave_request/';
        $smLeaveRequest = new SmLeaveRequest();
        $smLeaveRequest->staff_id = auth()->user()->id;
        $smLeaveRequest->role_id = auth()->user()->role_id;
        $smLeaveRequest->apply_date = date('Y-m-d', strtotime($request->apply_date));
        $smLeaveRequest->leave_define_id = $request->type_id;
        $smLeaveRequest->type_id = $leaveDefine->type_id;
        $smLeaveRequest->leave_from = date('Y-m-d', strtotime($request->leave_from));
        $smLeaveRequest->leave_to = date('Y-m-d', strtotime($request->leave_to));
        $smLeaveRequest->approve_status = 'P';
        $smLeaveRequest->reason = $request->reason;
        if ($request->file('attach_file')) {
            $smLeaveRequest->file = fileUpload($request->attach_file, $path);
        }

        $smLeaveRequest->school_id = auth()->user()->school_id;
        $smLeaveRequest->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $smLeaveRequest->save();

        try {
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

            $user = User::where('role_id', 1)->first();
            $smNotification = new SmNotification;
            $smNotification->user_id = $user->id;
            $smNotification->role_id = $user->role_id;
            $smNotification->date = date('Y-m-d');
            $smNotification->message = app('translator')->get('leave.leave_request');
            $smNotification->school_id = auth()->user()->school_id;
            $smNotification->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $smNotification->save();
            Notification::send($user, new LeaveApprovedNotification($smNotification));
        } catch (Exception $exception) {
            //
        }

        $data = [
            'id' => (int) $smLeaveRequest->id,
            'type_id' => (int) $smLeaveRequest->leave_define_id,
            'apply_date' => (string) $smLeaveRequest->apply_date,
            'leave_from' => (string) $smLeaveRequest->leave_from,
            'leave_to' => (string) $smLeaveRequest->leave_to,
            'reason' => (string) $smLeaveRequest->reason,
            'attach_file' => $smLeaveRequest->file ? (string) asset($smLeaveRequest->file) : (string) null,
        ];

        if ($data == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => [$data],
                'message' => 'Leave store successfully',
            ];
        }

        return response()->json($response);
    }
}
