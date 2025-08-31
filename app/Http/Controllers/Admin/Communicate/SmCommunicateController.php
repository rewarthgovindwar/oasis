<?php

namespace App\Http\Controllers\Admin\Communicate;

use App\User;
use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmParent;
use App\SmStudent;
use App\SmSmsGateway;
use App\SmEmailSmsLog;
use App\GlobalVariable;
use App\Jobs\sendSmsJob;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Log;
use Modules\Alumni\Entities\Alumni;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\RolePermission\Entities\InfixRole;
use App\Http\Requests\Admin\Communicate\SendEmailSmsRequest;
use Modules\University\Http\Controllers\UnCommunicateController;

class SmCommunicateController extends Controller
{
    public function sendEmailSmsView(Request $request)
    {
        /*
        try {
        */
            $roles = InfixRole::select('*')->where('is_saas', 0)->where('id', '!=', 1)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $classes = SmClass::get();
            return view('backEnd.communicate.sendEmailSms', ['roles' => $roles, 'classes' => $classes]);
        /*
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function sendEmailSms(SendEmailSmsRequest $sendEmailSmsRequest)
    {
        /*
        try {
        */
            $mobile_sms = SmSmsGateway::where('gateway_name', 'Mobile SMS')->first('device_info');
            $device_info = json_decode(@$mobile_sms->device_info);
            $device_status = @$device_info->status;
            if (moduleStatusCheck('University')) {
                $unCommunicateController = new UnCommunicateController();

                return $unCommunicateController->unEmailSms($sendEmailSmsRequest);
            }

            $smEmailSmsLog = new SmEmailSmsLog();
            $smEmailSmsLog->saveEmailSmsLogData($sendEmailSmsRequest);
            if (empty($sendEmailSmsRequest->selectTab) || $sendEmailSmsRequest->selectTab == 'G') {
                
                if (empty($sendEmailSmsRequest->role)) {
                    Toastr::error('Please select whom you want to send', 'Failed');

                    return redirect()->back();
                }

                if ($sendEmailSmsRequest->send_through == 'E') {
                    $to_name = '';
                    $to_email = [];
                    $to_mobile = [];
                    $receiverDetails = '';

                    foreach ($sendEmailSmsRequest->role as $role_id) {
                        if ($role_id == 2) {
                            $receiverDetails = SmStudent::select('email', 'full_name', 'mobile')
                                ->where('active_status', 1)
                                ->where('academic_id', getAcademicId())
                                ->get();
                        } elseif ($role_id == 3) {
                            $receiverDetails = SmParent::select('guardians_email as email', 'fathers_name as full_name', 'fathers_mobile as mobile')
                                ->where('active_status', 1)
                                ->where('academic_id', getAcademicId())
                                ->get();
                        } elseif ($role_id == GlobalVariable::isAlumni()) {
                            $receiverDetails = Alumni::with('student')
                                ->select('email', 'mobile', 'full_name')
                                ->get()
                                ->map(function ($alumni): array {
                                    return [
                                        'email' => optional($alumni->student)->email,
                                        'full_name' => optional($alumni->student)->full_name,
                                        'mobile' => $alumni->mobile ?? optional($alumni->student)->mobile,
                                    ];
                                });
                        } else {
                            $receiverDetails = SmStaff::select('email', 'full_name', 'mobile')
                                ->where('role_id', $role_id)
                                ->where('active_status', 1)
                                ->get();
                        }

                        foreach ($receiverDetails as $receiverDetail) {
                            $to_name = $receiverDetail->full_name;
                            $to_email[] = $receiverDetail->email;
                            $to_mobile[] = $receiverDetail->mobile;
                        }

                        $to_email = array_filter($to_email);
                    }

                    $compact['title'] = $sendEmailSmsRequest->email_sms_title;
                    $compact['description'] = $sendEmailSmsRequest->description;

                    foreach ($to_email as $reciverEmail) {
                        @send_mail($reciverEmail, $to_name, 'communication_sent_email', $compact);
                    }

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                if (activeSmsGateway() == null) {
                    Toastr::error('No SMS gateway found', 'Failed');

                    return redirect()->back();
                }
                $to_name = '';
                $to_email = '';
                $to_mobile = '';
                $receiverDetails = '';
                $receiver_numbers = [];
                foreach ($sendEmailSmsRequest->role as $role_id) {
                    if ($role_id == 2) {
                        $receiverDetails = SmStudent::select('email', 'full_name', 'mobile')
                            ->where('active_status', 1)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->get();
                    } elseif ($role_id == 3) {
                        $receiverDetails = SmParent::select('guardians_email as email', 'fathers_name as full_name', 'fathers_mobile as mobile')
                            ->where('school_id', Auth::user()->school_id)
                            ->where('academic_id', getAcademicId())
                            ->get();
                    } elseif ($role_id == GlobalVariable::isAlumni()) {
                        $receiverDetails = Alumni::with('student')
                            ->select('email', 'mobile', 'full_name')
                            ->get()
                            ->map(function ($alumni): array {
                                return [
                                    'email' => optional($alumni->student)->email,
                                    'full_name' => optional($alumni->student)->full_name,
                                    'mobile' => $alumni->mobile ?? optional($alumni->student)->mobile,
                                ];
                            });
                    } else {
                        $receiverDetails = SmStaff::select('email', 'full_name', 'mobile')
                            ->where('role_id', $role_id)
                            ->where('active_status', 1)
                            ->where('school_id', Auth::user()->school_id)
                            ->get();
                    }
                    foreach ($receiverDetails as $receiverDetail) {
                        $to_name = $receiverDetail->full_name;
                        $to_email = $receiverDetail->email;
                        $to_mobile = $receiverDetail->mobile;
                        if ($receiverDetail->mobile !== null) {
                            $receiver_numbers[] = $receiverDetail->mobile;
                        }

                        if (activeSmsGateway()->gateway_name !== 'Mobile SMS') {
                            @send_sms($to_mobile, 'communicate_sms', ['description' => $sendEmailSmsRequest->description]);
                        }
                    }

                    // Send SMS Convert to Flutter Notification Start
                    try {

                        if (activeSmsGateway()->gateway_name == 'Mobile SMS' && $device_status == 1) {
                            // config(['services.fcm.key' => apk_secret()]);
                            $user = User::find(Auth::user()->id);
                            $job = (new sendSmsJob($sendEmailSmsRequest->description, $sendEmailSmsRequest->email_sms_title, $receiver_numbers, $user))
                                ->delay(now()->addSeconds(2));
                            dispatch($job);                            
                        }
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }

                    // Send SMS Convert to Flutter Notification End
                }
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }
            if ($sendEmailSmsRequest->selectTab == 'I') {
                if (empty($sendEmailSmsRequest->message_to_individual)) {
                    Toastr::error('Please select whom you want to send', 'Failed');
                    return redirect()->back();
                }
                if ($sendEmailSmsRequest->send_through == 'E') {
                    $message_to_individual = $sendEmailSmsRequest->message_to_individual;
                    $to_email = [];
                    $to_mobile = [];
                    foreach ($message_to_individual as $value) {
                        $receiver_full_name_email = explode('-', $value);
                        $receiver_full_name = $receiver_full_name_email[0];
                        $receiver_email = $receiver_full_name_email[1];
                        $receiver_mobile = $receiver_full_name_email[2];
                        $to_name = $receiver_full_name;
                        $to_email[] = $receiver_email;
                        $to_mobile[] = $receiver_mobile;
                    }

                    $to_email = array_filter($to_email);

                    $compact['title'] = $sendEmailSmsRequest->email_sms_title;
                    $compact['description'] = $sendEmailSmsRequest->description;

                    foreach ($to_email as $reciverEmail) {
                        @send_mail($reciverEmail, $to_name, 'communication_sent_email', $compact);
                    }

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                if (activeSmsGateway() == null) {
                    Toastr::error('No SMS gateway found', 'Failed');

                    return redirect()->back();
                }

                $message_to_individual = $sendEmailSmsRequest->message_to_individual;
                $receiver_numbers = [];
                foreach ($message_to_individual as $value) {
                    $receiver_full_name_email = explode('-', $value);
                    $receiver_full_name = $receiver_full_name_email[0];
                    $receiver_email = $receiver_full_name_email[1];
                    $receiver_mobile = $receiver_full_name_email[2];
                    $to_name = $receiver_full_name;
                    $to_email = $receiver_email;
                    $to_mobile = $receiver_mobile;
                    $receiver_numbers[] = $to_mobile;

                    if (activeSmsGateway()->gateway_name !== 'Mobile SMS') {
                        @send_sms($to_mobile, 'communicate_sms', ['description' => $sendEmailSmsRequest->description]);
                    }
                }

                // Send SMS Convert to Flutter Notification Start
                if (activeSmsGateway()->gateway_name == 'Mobile SMS' && $device_status == 1) {
                    config(['services.fcm.key' => apk_secret()]);
                    $user = User::find(Auth::user()->id);
                    $job = (new sendSmsJob($sendEmailSmsRequest->description, $sendEmailSmsRequest->email_sms_title, $receiver_numbers, $user))
                        ->delay(now()->addSeconds(2));
                    dispatch($job);
                }

                // Send SMS Convert to Flutter Notification End
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            }

            if (empty($sendEmailSmsRequest->message_to_section)) {
                Toastr::error('Please select whom you want to send', 'Failed');
                return redirect()->back();
            }

            if ($sendEmailSmsRequest->send_through == 'E') {
                $class_id = $sendEmailSmsRequest->class_id;
                $selectedSections = $sendEmailSmsRequest->message_to_section;
                $to_email = [];
                $to_mobile = [];
                foreach ($sendEmailSmsRequest->message_to_student_parent as $message) {
                    foreach ($selectedSections as $selectedSection) {
                        $student_ids = StudentRecord::where('class_id', $class_id)
                            ->where('section_id', $selectedSection)
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', auth()->user()->school_id)
                            ->pluck('student_id')->unique();
                        if ($message == 2) {
                            $students = SmStudent::select('email', 'full_name', 'mobile')
                                ->whereIn('id', $student_ids)
                                ->where('active_status', 1)
                                ->get();
                            foreach ($students as $student) {
                                $to_name = $student->full_name;
                                $to_email[] = $student->email;
                                $to_mobile[] = $student->mobile;
                            }

                            $to_email = array_filter($to_email);
                        }

                        if ($message == 3) {
                            $parents = SmStudent::with(['parents' => function ($q): void {
                                $q->select('id', 'guardians_email', 'guardians_name', 'guardians_mobile');
                            }])
                                ->whereIn('id', $student_ids)
                                ->where('active_status', 1)
                                ->get();
                            foreach ($parents as $parent) {
                                $to_name = $parent->parents->guardians_name;
                                $to_email[] = $parent->parents->guardians_email;
                                $to_mobile[] = $parent->parents->guardians_mobile;
                            }

                            $to_email = array_filter($to_email);
                        }
                    }
                }

                $compact['title'] = $sendEmailSmsRequest->email_sms_title;
                $compact['description'] = $sendEmailSmsRequest->description;

                foreach ($to_email as $reciverEmail) {
                    @send_mail($reciverEmail, $to_name, 'communication_sent_email', $compact);
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            if (activeSmsGateway() == null) {
                Toastr::error('No SMS gateway found', 'Failed');
                return redirect()->back();
            }
            $class_id = $sendEmailSmsRequest->class_id;
            $selectedSections = $sendEmailSmsRequest->message_to_section;
            foreach ($sendEmailSmsRequest->message_to_student_parent as $message) {
                foreach ($selectedSections as $selectedSection) {
                    $student_ids = StudentRecord::where('class_id', $class_id)
                        ->where('section_id', $selectedSection)
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', auth()->user()->school_id)
                        ->pluck('student_id')->unique();
                        
                    if ($message == 2) {
                        $students = SmStudent::select('email', 'full_name', 'mobile')
                            ->whereIn('id', $student_ids)
                            ->where('active_status', 1)
                            ->get();
                        $receiver_numbers = [];
                        foreach ($students as $student) {
                            $to_name = $student->full_name;
                            $to_email = $student->email;
                            $to_mobile = $student->mobile;
                            if ($to_mobile !== null) {
                                $receiver_numbers[] = $to_mobile;
                            }

                            if (activeSmsGateway()->gateway_name !== 'Mobile SMS') {
                                @send_sms($to_mobile, 'communicate_sms', ['description' => $sendEmailSmsRequest->description]);
                            }
                        }
                    }

                    if ($message == 3) {
                        $parents = SmStudent::with(['parents' => function ($q): void {
                            $q->select('id', 'guardians_email', 'guardians_name', 'guardians_mobile');
                        }])
                            ->whereIn('id', $student_ids)
                            ->where('active_status', 1)
                            ->get();
                        $receiver_numbers = [];
                        foreach ($parents as $parent) {
                            $to_name = $parent->parents->guardians_name;
                            $to_email = $parent->parents->guardians_email;
                            $to_mobile = $parent->parents->guardians_mobile;
                            if ($to_mobile !== null) {
                                $receiver_numbers[] = $to_mobile;
                            }

                            if (activeSmsGateway()->gateway_name !== 'Mobile SMS') {
                                @send_sms($to_mobile, 'communicate_sms', ['description' => $sendEmailSmsRequest->description]);
                            }
                        }
                    }

                    // Send SMS Convert to Flutter Notification Start
                    if (activeSmsGateway()->gateway_name == 'Mobile SMS' && apk_secret() && $device_status == 1) {
                        // config(['services.fcm.key' => apk_secret()]);
                        $user = User::find(Auth::user()->id);
                        $job = (new sendSmsJob($sendEmailSmsRequest->description, $sendEmailSmsRequest->email_sms_title, $receiver_numbers, $user))
                            ->delay(now()->addSeconds(2));
                        dispatch($job);
                    }

                    // Send SMS Convert to Flutter Notification End
                }
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studStaffByRole(Request $request)
    {
        /*
        try {
        */
            if ($request->id == 2) {
                $allStudents = SmStudent::where('active_status', '=', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
                $students = [];
                foreach ($allStudents as $allStudent) {
                    $students[] = SmStudent::find($allStudent->id);
                }

                return response()->json([$students]);
            }

            if ($request->id == 3) {
                $Parents = SmParent::where('school_id', Auth::user()->school_id)
                    ->get();

                return response()->json([$Parents]);
            }

            if ($request->id == GlobalVariable::isAlumni()) {
                $allAlumnis = Alumni::where('school_id', Auth::user()->school_id)->with('student')
                    ->get();
                $alumnis = [];
                foreach ($allAlumnis as $allAlumni) {
                    $alumnis[] = Alumni::find($allAlumni->id)->student;
                }

                return response()->json([$alumnis]);
            }

            if ($request->id != 2 && $request->id != 3) {
                $allStaffs = SmStaff::whereRole($request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('active_status', '=', 1)
                    ->get();
                $staffs = [];
                foreach ($allStaffs as $allStaff) {
                    $staffs[] = SmStaff::find($allStaff->id);
                }

                return response()->json([$staffs]);
            }
        /*
            } catch (Exception $exception) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
        */
    }

    public function emailSmsLog()
    {
        /*
        try {
        */
            $emailSmsLogs = SmEmailSmsLog::where('academic_id', getAcademicId())
                ->orderBy('id', 'DESC')
                ->where('school_id', Auth::user()->school_id)
                ->get();
            // return getAcademicId();
            return view('backEnd.communicate.emailSmsLog', ['emailSmsLogs' => $emailSmsLogs]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
