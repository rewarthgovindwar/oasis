<?php

use App\User;
use App\SmExam;
use App\SmClass;
use App\SmStaff;
use App\SmStyle;
use App\SmParent;
use App\SmSchool;
use App\SmStudent;
use App\SmSubject;
use App\SmExamType;
use App\SmLanguage;
use App\SmAddIncome;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmsTemplate;
use Clickatell\Rest;
use App\Models\Shift;
use App\SmDateFormat;
use App\SmFeesMaster;
use App\SmMarksGrade;
use App\SmSmsGateway;
use App\Jobs\EmailJob;
use App\SmFeesPayment;
use App\SmResultStore;
use GuzzleHttp\Client;
use App\SmAcademicYear;
use App\SmClassTeacher;
use App\SmEmailSetting;
use App\SmExamSchedule;
use App\SmNotification;
use BaconQrCode\Writer;
use App\SmAssignSubject;
use App\SmExamAttendance;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use App\InfixModuleManager;
use App\Models\FeesInvoice;
use App\SmFeesCarryForward;
use Illuminate\Support\Str;
use App\CustomResultSetting;
use App\SmHeaderMenuManager;
use App\SmSubjectAttendance;
use App\Models\StudentRecord;
use App\SmExamAttendanceChild;
use Illuminate\Support\Carbon;
use App\Models\SmExamSignature;
use App\SmClassOptionalSubject;
use App\Models\CustomSmsSetting;
use App\SmOptionalSubjectAssign;
use App\Models\ExamMeritPosition;
use App\Models\SmCalendarSetting;
use App\Models\DirectFeesReminder;
use Illuminate\Support\Facades\DB;
use App\Models\AllExamWisePosition;
use App\Models\FeesCarryForwardLog;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\GlobalAcademicScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\SmNotificationSetting;
use Illuminate\Support\Facades\Cache;
use Modules\Fees\Entities\FmFeesType;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Larabuild\Pagebuilder\Models\Page;
use BaconQrCode\Renderer\ImageRenderer;
use Illuminate\Support\Facades\Storage;
use Modules\Lms\Entities\CourseSetting;
use App\Models\FeesCarryForwardSettings;
use Modules\Forum\Entities\ForumSetting;
use App\Models\SmStudentRegistrationField;
use App\Models\DirectFeesInstallmentAssign;
use Modules\MenuManage\Entities\MenuManage;
use Modules\RolePermission\Entities\Permission;
use Modules\University\Entities\UnAcademicYear;
use Modules\Fees\Entities\FmFeesInvoiceSettings;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Modules\University\Entities\UnFeesInstallmentAssign;
use Modules\ParentRegistration\Entities\SmStudentRegistration;
use Modules\QRCodeAttendance\Entities\QRCodeAttendanceSetting;

function sendEmailBio(array $data, $to_name, $to_email, $email_sms_title)
{
    $systemSetting = DB::table('sm_general_settings')->select('school_name', 'email')->find(1);
    $systemEmail = DB::table('sm_email_settings')->find(1);
    $system_email = $systemEmail->from_email;
    $school_name = $systemSetting->school_name;
    if (! empty($system_email)) {
        $data['email_sms_title'] = $email_sms_title;
        $data['system_email'] = $system_email;
        $data['school_name'] = $school_name;
        $details = $to_email;
        dispatch(new \App\Jobs\SendEmailJob($data, $details));
        $error_data = [];

        return true;
    }

    $error_data[0] = 'success';
    $error_data[1] = 'Operation Failed, Please Updated System Mail';

    return $error_data;

}

if (! function_exists('activeSmsGateway')) {
    function activeSmsGateway()
    {
        $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;

        return SmSmsGateway::where('school_id', $school_id)->where('active_status', '=', 1)->first();
    }
}

if (! function_exists('youtubeVideo')) {
    function youtubeVideo($video_url)
    {
        if (Str::contains($video_url, 'youtu.be')) {
            $url = explode('/', $video_url);

            return 'https://www.youtube.com/watch?v='.$url[3];
        }

        if (Str::contains($video_url, '&')) {
            return mb_substr($video_url, 0, mb_strpos($video_url, '&'));
        }

        return $video_url;

    }
}

function showFileName($data): string
{
    $name = explode('/', $data);
    $number = array_key_last($name);

    return $name[$number];
}

function sendSMSApi(string $to_mobile, string $sms, $id)
{
    $activeSmsGateway = SmSmsGateway::find($id);
    if ($activeSmsGateway->gateway_name == 'Twilio') {
        if (! $activeSmsGateway->twilio_account_sid || $activeSmsGateway->twilio_authentication_token) {
            return null;
        }

        $client = new Twilio\Rest\Client($activeSmsGateway->twilio_account_sid, $activeSmsGateway->twilio_authentication_token);
        if ($to_mobile !== '' && $to_mobile !== '0') {
            return $message = $client->messages->create($to_mobile, ['from' => $activeSmsGateway->twilio_registered_no, 'body' => $sms]);
        }
    } // end Twilio
    elseif ($activeSmsGateway->gateway_name == 'Clickatell') {

        // config(['clickatell.api_key' => $activeSmsGateway->clickatell_api_id]); //set a variale in config file(clickatell.php)

        $clickatell = new Rest();
        $result = $clickatell->sendMessage(['to' => $to_mobile, 'content' => $sms]);
    } // end Clickatell

    // start Himalayasms

    elseif ($activeSmsGateway->gateway_name == 'Himalayasms') {
        $client = new Client();
        $request = $client->get('https://sms.techhimalaya.com/base/smsapi/index.php', [
            'query' => [
                'key' => $activeSmsGateway->himalayasms_key,
                'senderid' => $activeSmsGateway->himalayasms_senderId,
                'campaign' => $activeSmsGateway->himalayasms_campaign,
                'routeid' => $activeSmsGateway->himalayasms_routeId,
                'contacts' => $to_mobile,
                'msg' => $sms,
                'type' => 'text',
            ],
            'http_errors' => false,
        ]);

        $result = $request->getBody();
    } elseif ($activeSmsGateway->gateway_name == 'Msg91') {
        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
        $msg91_route = $activeSmsGateway->msg91_route;
        $msg91_country_code = $activeSmsGateway->msg91_country_code;

        $curl = curl_init();

        $url = 'https://api.msg91.com/api/sendhttp.php?mobiles='.$to_mobile.'&authkey='.$msg91_authentication_key_sid.'&route='.$msg91_route.'&sender='.$msg91_sender_id.'&message='.$sms.'&country=91';

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $result = $err !== '' && $err !== '0' ? 'cURL Error #:'.$err : $response;
    }

    // end Msg91
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err !== '' && $err !== '0') {
        return 'cURL Error #:'.$err;
    }

    return $response;
} // end Msg91

function sendSMSBio(string $to_mobile, string $sms)
{
    $activeSmsGateway = SmSmsGateway::where('school_id', Auth::user()->school_id)->where('active_status', '=', 1)->first();
    if ($activeSmsGateway->gateway_name == 'Twilio') {

        config(['TWILIO.SID' => $activeSmsGateway->twilio_account_sid]);
        config(['TWILIO.TOKEN' => $activeSmsGateway->twilio_authentication_token]);
        config(['TWILIO.FROM' => $activeSmsGateway->twilio_registered_no]);
        $account_id = $activeSmsGateway->twilio_account_sid; // Your Account SID from www.twilio.com/console
        $auth_token = $activeSmsGateway->twilio_authentication_token; // Your Auth Token from www.twilio.com/console
        $from_phone_number = $activeSmsGateway->twilio_registered_no;
        $client = new Twilio\Rest\Client($account_id, $auth_token);
        if ($to_mobile !== '' && $to_mobile !== '0') {
            return $message = $client->messages->create($to_mobile, ['from' => $from_phone_number, 'body' => $sms]);
        }
    } // end Twilio
    elseif ($activeSmsGateway->gateway_name == 'Clickatell') {

        // config(['clickatell.api_key' => $activeSmsGateway->clickatell_api_id]); //set a variale in config file(clickatell.php)

        $clickatell = new Rest();
        $result = $clickatell->sendMessage(['to' => $to_mobile, 'content' => $sms]);
    } // end Clickatell

    elseif ($activeSmsGateway->gateway_name == 'Msg91') {
        $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
        $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
        $msg91_route = $activeSmsGateway->msg91_route;
        $msg91_country_code = $activeSmsGateway->msg91_country_code;

        $curl = curl_init();

        $url = 'https://api.msg91.com/api/sendhttp.php?mobiles='.$to_mobile.'&authkey='.$msg91_authentication_key_sid.'&route='.$msg91_route.'&sender='.$msg91_sender_id.'&message='.$sms.'&country=91';

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $result = $err !== '' && $err !== '0' ? 'cURL Error #:'.$err : $response;
    } // end Msg91
    elseif ($activeSmsGateway->gateway_name == 'TextLocal') {

        // Account details
        // $apiKey = urlencode('Your apiKey');
        $apiKey = $activeSmsGateway->textlocal_hash;
        $url = $activeSmsGateway->type == 'in' ? 'http://api.textlocal.in/send/' : 'http://api.txtlocal.com/send/';
        // Message details
        $numbers = $to_mobile;
        $sender = urlencode($activeSmsGateway->textlocal_sender);
        $message = rawurlencode($sms);

        // $numbers = implode(',', $numbers);

        // Prepare data for POST request
        $data = ['apikey' => $apiKey, 'numbers' => $numbers, 'sender' => $sender, 'message' => $message];

        // Send the POST request with cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here
        $result = $response;
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err !== '' && $err !== '0') {
        return 'cURL Error #:'.$err;
    }

    return $response;
} // end Msg91

function getValueByString($student_id, $string, $extra = null)
{
    $student = SmStudent::find($student_id);
    if ($extra !== null) {
        return $student->$string->$extra;
    }

    return $student->$string;

}

function getParentName($student_id, $string, $extra = null)
{
    $student = SmStudent::find($student_id);
    $parent = SmParent::where('id', $student->parent_id)->first();
    if ($extra !== null) {
        return $student->$parent->$extra;
    }

    return $parent->fathers_name;

}

function SMSBody($body, $s_id, $time)
{
    try {
        $original_message = $body;
        // $original_message= "Dear Parent [fathers_name], your child [class] came to the school at [section]";
        $chars = preg_split('/[\s,]+/', $original_message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($chars as $char) {
            if (mb_strstr($char[0], '[')) {
                $str = str_replace('[', '', $char);
                $str = str_replace(']', '', $str);
                $str = str_replace('.', '', $str);
                if ($str == 'class') {
                    $str = 'class';
                    $extra = 'class_name';
                    $custom_array[$char] = getValueByString($s_id, $str, $extra);
                } elseif ($str == 'section') {
                    $str = 'section';
                    $extra = 'section_name';
                    $custom_array[$char] = getValueByString($s_id, $str, $extra);
                } elseif ($str == 'check_in_time') {
                    $custom_array[$char] = $time;
                } elseif ($str == 'fathers_name') {
                    $str = 'parents';
                    $extra = 'fathers_name';
                    $custom_array[$char] = getValueByString($s_id, $str, $extra);
                    // $custom_array[$item]= 'father';
                } else {
                    $custom_array[$char] = getValueByString($s_id, $str);
                }
            }
        }

        foreach ($custom_array as $key => $value) {
            $original_message = str_replace($key, $value, $original_message);
        }

        return $original_message;
    } catch (Exception $exception) {
        return [];
    }
}

function FeesDueSMSBody($body, $s_id, $time)
{
    try {
        $original_message = $body;
        $chars = preg_split('/[\s,]+/', $original_message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($chars as $char) {
            if (mb_strstr($char[0], '|')) {
                $str = str_replace('|', '', $char);
                // return $str;
                $str = str_replace('|', '', $str);
                $str = str_replace('.', '', $str);
                if ($str == 'StudentName') {
                    $str = 'StudentName';
                    $extra = 'full_name';
                    $custom_array[$char] = getValueByString($s_id, $str, $extra);
                } elseif ($str == 'fathers_name') {
                    $str = 'parents';
                    $extra = 'fathers_name';
                    $custom_array[$char] = getValueByString($s_id, $str, $extra);
                    // $custom_array[$item]= 'father';
                } else {
                    $custom_array[$char] = getValueByString($s_id, $str);
                }
            }
        }

        foreach ($custom_array as $key => $value) {
            $original_message = str_replace($key, $value, $original_message);
        }

        return $original_message;
    } catch (Exception $exception) {
        return [];
    }
}

if (! function_exists('userPermission')) {
    function userPermission($route, $role_id = null, $purpose = null): bool
    {
        
        $role_id = Auth::user()->role_id;
        $permissions = app('permission');
        if ($role_id == 1 && Auth::user()->is_administrator == 'yes') {
            return true;
        }
        if ((! empty($permissions)) && ($role_id != 1)) {
            return @in_array($route, $permissions);
        }

        if (moduleStatusCheck('Saas') == true) {
            $saas_status_ids = app('saasSettings');

            return ! @in_array($route, $saas_status_ids);
        }

        return true;

    }
}

if (! function_exists('moduleStatusCheck')) {
    function moduleStatusCheck(string $module)
    {
        try {
            $all_module = session()->get('all_module');
            $moduleStatus = true;

            if (empty($all_module)) {
                $all_module = [];
                $modules = InfixModuleManager::select('name')->get();
                foreach ($modules as $module) {
                    $all_module[] = $module->name;
                }

                session()->put('all_module', $all_module);
            }

            $is_module_available = 'Modules/'.$module.'/Providers/'.$module.'ServiceProvider.php';
            if (file_exists($is_module_available)) {
                $moduleStatus = Module::find($module)->isDisabled();
            }

            if (! in_array($module, $all_module) || $moduleStatus) {
                return false;
            }

            $is_verify = Cache::rememberForever('module_'.$module, function () use ($module) {
                return InfixModuleManager::where('name', $module)->first();
            });

            if (! $is_verify || ! $is_verify->purchase_code) {
                return false;
            }

            if ($module !== 'Saas' && isSubscriptionEnabled() && in_array($module, planPermissions('modules') ?? [])) {
                return isModuleForSchool($module);
            }

            return true;
        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('dateConvert')) {

    function dateConvert($input_date)
    {
        try {
            $system_date_format = session()->get('system_date_format');
            if (empty($system_date_format)) {
                $date_format_id = SmGeneralSettings::where('id', 1)->first(['date_format_id'])->date_format_id;
                $system_date_format = SmDateFormat::where('id', $date_format_id)->first(['format'])->format;
                session()->put('system_date_format', $system_date_format);
            }

            return \Carbon\Carbon::parse($input_date)->format($system_date_format);
        } catch (Throwable $throwable) {
            return $input_date;
        }
    }
}

if (! function_exists('dateTimeConvert')) {

    function dateTimeConvert($input_date_time)
    {
        try {
            $system_date_format = session()->get('system_date_format').' g:i A';
            if ($system_date_format == '0') {
                $date_format_id = SmGeneralSettings::where('id', 1)->first(['date_format_id'])->date_format_id;
                $system_date_format = SmDateFormat::where('id', $date_format_id)->first(['format'])->format.' g:i A';
                session()->put('system_date_format', $system_date_format);
            }

            return \Carbon\Carbon::parse($input_date_time)->format($system_date_format);
        } catch (Throwable $throwable) {
            return $input_date_time;
        }
    }
}

if (! function_exists('convertTime')) {
    function convertTime($time): string
    {
        return date('g:i A', strtotime($time));
    }
}

if (! function_exists('getAcademicId')) {
    function getAcademicId()
    {

        if (session()->has('sessionId')) {
            return session()->get('sessionId');
        }

        if (moduleStatusCheck('University')) {
            $session_id = generalSetting()->un_academic_id;
            if (! $session_id) {
                $session_id = UnAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first()->id;
            }
        } else {
            $session_id = generalSetting()->session_id;
            if (! $session_id) {
                $session_id = SmAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first()->id;
            }
        }

        session()->put('sessionId', $session_id);

        return session()->get('sessionId');

    }
}

if (! function_exists('timeZone')) {
    function timeZone()
    {
        $time_zone_setup = session()->get('time_zone_setup');
        if (is_null($time_zone_setup)) {
            $time_zone = SmGeneralSettings::join('sm_time_zones', 'sm_time_zones.id', '=', 'sm_general_settings.time_zone_id')
                ->where('school_id', 1)->first('time_zone');
            session()->put('time_zone_setup', $time_zone);
            $time_zone_setup = session()->get('time_zone_setup');
        }

        return $time_zone_setup->time_zone;
    }
}

if (! function_exists('schoolTimeZone')) {
    function schoolTimeZone()
    {
        $time_zone_setup = session()->get('time_zone_setup');
        if (is_null($time_zone_setup)) {
            $time_zone = SmGeneralSettings::join('sm_time_zones', 'sm_time_zones.id', '=', 'sm_general_settings.time_zone_id')
                ->where('school_id', Auth::user()->school_id)->first('time_zone');
            session()->put('time_zone_setup', $time_zone);
            $time_zone_setup = session()->get('time_zone_setup');
        }

        return $time_zone_setup->time_zone;
    }
}

if (! function_exists('getUserLanguage')) {
    function getUserLanguage()
    {

        if (Auth::check()) {
            return userLanguage();
        }

        $school_id = app()->bound('school') ? app('school')->id : 1;
        $user = User::where('role_id', 1)->where('school_id', $school_id)->first();

        return $user ? $user->language : 'en';

    }
}

if (! function_exists('checkAdmin')) {
    function checkAdmin(): ?bool
    {
        if (Auth::check()) {
            if (Auth::user()->is_administrator == 'yes') {
                return true;
            }

            return Auth::user()->is_saas == 1;
        }

        return null;
    }
}

if (! function_exists('getTempleteDetails')) {
    function getTempleteDetails($purpose, $type = null)
    {
        $data = SmsTemplate::query();
        $data = $data->where('purpose', $purpose)->where('status', 1);
        if ($type) {
            $data->where('type', $type);
        }

        if (Auth::check()) {
            return $data->where('school_id', Auth::user()->school_id)->first();
        }

        return $data->first();
    }
}

if (! function_exists('send_mail')) {
    function send_mail($reciver_email, $receiver_name, $purpose, $data = []): void
    {
        if (! $reciver_email) {
            return;
        }

        $templete = getTempleteDetails($purpose, 'email');
        if (! $templete) {
            return;
        }

        $school_id = Auth::check() && saasSettings('email_settings') ? Auth::user()->school_id : 1;

        $setting = SmEmailSetting::where('school_id', $school_id)->where('active_status', 1)->first();

        if (! $setting) {
            return;
        }

        $sender_email = $setting->from_email;
        $sender_name = $setting->from_name;
        $email_driver = $setting->mail_driver;

        $subject = getTempleteDetails($purpose, 'email')->subject;

        $body = SmsTemplate::emailTempleteToBody(getTempleteDetails($purpose, 'email')->body, $data);
        view('backEnd.email.emailBody', ['body' => $body]);

        try {
            if (Schema::hasTable('sm_email_settings')) {
                if ($email_driver == 'smtp') {
                    $config = Auth::check() ? DB::table('sm_email_settings')
                        ->where('school_id', Auth::user()->school_id)
                        ->where('mail_driver', 'smtp')
                        ->first() :
                        DB::table('sm_email_settings')
                            ->where('mail_driver', 'smtp')
                            ->first();

                    if ($config) {
                        Config::set('mail.default', 'smtp');
                        Config::set('mail.from.from', $config->mail_username);
                        Config::set('mail.from.name', $config->from_name);
                        Config::set('mail.mailers.smtp.host', $config->mail_host);
                        Config::set('mail.mailers.smtp.port', $config->mail_port);
                        Config::set('mail.mailers.smtp.username', $config->mail_username);
                        Config::set('mail.mailers.smtp.password', $config->mail_password);
                        Config::set('mail.mailers.smtp.encryption', $config->mail_encryption);
                    }
                } else {
                    Config::set('mail.default', 'sendmail');
                }
            }

            $emailData['driver'] = $email_driver;
            $emailData['reciver_email'] = $reciver_email;
            $emailData['receiver_name'] = $receiver_name;
            $emailData['sender_name'] = $sender_name;
            $emailData['sender_email'] = $sender_email;
            $emailData['subject'] = $subject;

            dispatch(new EmailJob($body, $emailData));
        } catch (Exception $exception) {
            Log::info($exception);
        }
    }
}

if (! function_exists('send_mail_without_template')) {
    function send_mail_without_template($reciver_email, $receiver_name, $subject, $view, $compact = []): void
    {

        $school_id = Auth::check() && saasSettings('email_settings') ? Auth::user()->school_id : 1;

        $setting = SmEmailSetting::where('school_id', $school_id)->where('active_status', 1)->first();

        if (! $setting) {
            return;
        }

        $sender_email = $setting->from_email;
        $sender_name = $setting->from_name;
        $email_driver = $setting->mail_driver;
        $view = view($view, $compact);
        try {
            if ($email_driver == 'smtp') {
                if (Schema::hasTable('sm_email_settings')) {
                    $config = Auth::check() ? DB::table('sm_email_settings')
                        ->where('school_id', Auth::user()->school_id)
                        ->where('mail_driver', 'smtp')
                        ->first() :
                        DB::table('sm_email_settings')
                            ->where('mail_driver', 'smtp')
                            ->first();

                    if ($config) {
                        Config::set('mail.driver', $config->mail_driver);
                        Config::set('mail.from', $config->mail_username);
                        Config::set('mail.name', $config->from_name);
                        Config::set('mail.host', $config->mail_host);
                        Config::set('mail.port', $config->mail_port);
                        Config::set('mail.username', $config->mail_username);
                        Config::set('mail.password', $config->mail_password);
                        Config::set('mail.encryption', $config->mail_encryption);
                    }
                }

                Mail::send('backEnd.email.emailBody', ['body' => $body], function ($message) use ($reciver_email, $receiver_name, $sender_name, $sender_email, $subject): void {
                    $message->to($reciver_email, $receiver_name)->subject($subject);
                    $message->from($sender_email, $sender_name);
                });
            }

            if ($email_driver == 'php') {
                $message = (string) $view;
                $headers = "From: <{$sender_email}> \r\n";
                $headers .= "Reply-To: {$receiver_name} <{$reciver_email}> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($reciver_email, $subject, $message, $headers);
            }
        } catch (Exception $exception) {
            Log::info($exception);
        }
    }
}

if (! function_exists('getFileName')) {
    function getFileName($data): string
    {
        if ($data) {
            $name = explode('/', $data);

            return $name[count($name) - 1] ?? $name[0];
        }

        return '';

    }
}

// Get File Path From HELPER

if (! function_exists('getFilePath3')) {
    function getFilePath3($data): string
    {

        if ($data) {
            $name = explode('/', $data);

            return $name[3] ?? $name[0];
        }

        return '';

    }
}

if (! function_exists('getFilePath4')) {
    function getFilePath4($data): string
    {
        if ($data) {
            $name = explode('/', $data);
            if ($name[4] !== '' && $name[4] !== '0') {
                return $name[3];
            }

            return '';

        }

        return '';

    }
}

if (! function_exists('getFilePath5')) {
    function getFilePath5($data): string
    {
        if ($data) {
            $name = explode('/', $data);
            if ($name[5] !== '' && $name[5] !== '0') {
                return $name[5];
            }

            return '';

        }

        return '';

    }
}

if (! function_exists('showPicName')) {
    function showPicName($data): ?string
    {
        try {
            if ($data) {
                $name = explode('/', $data);
                if ($name[4] !== '' && $name[4] !== '0') {
                    return $name[4];
                }

                return '';

            }

            return '';

        } catch (Exception $exception) {
            return null;
        }
    }
}

if (! function_exists('showJoiningLetter')) {
    function showJoiningLetter($data): string
    {
        $name = explode('/', $data);

        return $name[3];
    }
}

if (! function_exists('showResume')) {
    function showResume($data): string
    {
        $name = explode('/', $data);

        return $name[3];
    }
}

if (! function_exists('showDocument')) {
    function showDocument($data): string
    {
        @$name = explode('/', @$data);
        if (@$name[4] !== '' && @$name[4] !== '0') {

            return $name[4];
        }

        return '';

    }
}

// end get file path from helpers

if (! function_exists('termResult')) {
    function termResult($exam_id, $class_id, $section_id, $student_id, $subject_count)
    {
        try {
            $assigned_subject = SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();
            $mark_store = DB::table('sm_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id]])->first();
            $subject_marks = [];
            $subject_gpas = [];
            foreach ($assigned_subject as $subject) {
                $subject_mark = DB::table('sm_mark_stores')->where([['class_id', $class_id], ['section_id', $section_id], ['exam_term_id', $exam_id], ['student_id', $student_id], ['subject_id', $subject->subject_id]])->first();
                $custom_result = new CustomResultSetting; // correct

                $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);
                // return $subject_mark;
                $subject_marks[$subject->subject_id][0] = $subject_mark->total_marks;
                $subject_marks[$subject->subject_id][1] = $subject_gpa;
                $subject_gpas[$subject->subject_id] = $subject_gpa;
            }

            $total_gpa = array_sum($subject_gpas);

            return $total_gpa / $subject_count;
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('getFinalResult')) {
    function getFinalResult($exam_id, $class_id, $section_id, $student_id, $percentage)
    {
        try {
            $system_setting = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $system_setting = $system_setting->session_id;
            $custom_result_setup = CustomResultSetting::where('academic_year', $system_setting)->first();

            $assigned_subject = SmAssignSubject::where('class_id', $class_id)->where('section_id', $section_id)->get();

            $all_subjects_gpa = [];
            foreach ($assigned_subject as $subject) {
                $custom_result = new CustomResultSetting;
                $subject_gpa = $custom_result->getSubjectGpa($exam_id, $class_id, $section_id, $student_id, $subject->subject_id);
                $all_subjects_gpa[] = $subject_gpa[$subject->subject_id][1];
            }

            $percentage = $custom_result_setup->$percentage;
            $term_gpa = array_sum($all_subjects_gpa) / $assigned_subject->count();
            $percentage = number_format((float) $percentage, 2, '.', '');

            return ($percentage / 100) * $term_gpa;
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('getSubjectGpa')) {
    function getSubjectGpa($class_id, $section_id, $exam_id, $student_id, $subject)
    {
        try {
            $subject_marks = [];
            $subject_mark = DB::table('sm_mark_stores')->where('student_id', $student_id)->where('exam_term_id', '=', $exam_id)->first();

            $custom_result = new CustomResultSetting;
            $subject_gpa = $custom_result->getGpa($subject_mark->total_marks);

            $subject_marks[$subject][0] = $subject_mark->total_marks;
            $subject_marks[$subject][1] = $subject_gpa;

            // return $subject_mark->total_marks;
            return $subject_marks;
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('getGrade')) {
    function getGrade($marks, $description = null)
    {
        try {
            if ($description) {
                $marks_gpa = DB::table('sm_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)
                    ->where('academic_id', getAcademicId())->first();

                return $marks_gpa->description;
            }

            $marks_gpa = DB::table('sm_marks_grades')->where('percent_from', '<=', $marks)->where('percent_upto', '>=', $marks)
                ->where('academic_id', getAcademicId())->first();

            return $marks_gpa->grade_name;

        } catch (Exception $exception) {
            return 'Undefined';
        }
    }
}

if (! function_exists('getNumberOfPart')) {
    function getNumberOfPart($subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            return SmExamSetup::where([
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('GetResultBySubjectId')) {
    function GetResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            return SmMarkStore::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_id],
                ['student_id', $student_id],
                ['subject_id', $subject_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('GetFinalResultBySubjectId')) {
    function GetFinalResultBySubjectId($class_id, $section_id, $subject_id, $exam_id, $student_id)
    {

        try {
            return SmResultStore::where([
                ['class_id', $class_id],
                ['section_id', $section_id],
                ['exam_type_id', $exam_id],
                ['student_id', $student_id],
                ['subject_id', $subject_id],
            ])->first();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('markGpa')) {
    function markGpa($marks)
    {
        $mark = SmMarksGrade::where([
            ['percent_from', '<=', floor($marks)],
            ['percent_upto', '>=', floor($marks)],
        ])
            ->first();
        if ($mark) {
            return $mark;
        }

        $fail_grade = SmMarksGrade::min('gpa');

        return SmMarksGrade::where('gpa', $fail_grade)->first();

    }
}

if (! function_exists('getGrade')) {
    function getGrade($grade)
    {
        $mark = SmMarksGrade::where('from', '<=', $grade)->where('up', '>=', $grade)->where('academic_id', getAcademicId())->first();
        if ($mark) {
            return $mark;
        }

        $fail_grade = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->min('gpa');

        return SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->where('gpa', $fail_grade)
            ->first();

    }
}

if (! function_exists('getGradeUpdate')) {
    function getGradeUpdate($grade)
    {
        $mark = SmMarksGrade::where('from', '<=', $grade)->where('up', '>=', $grade)->where('academic_id', getAcademicId())->first();
        if ($mark) {
            return $mark;
        }

        $fail_grade = SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->min('gpa');

        return SmMarksGrade::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->where('gpa', $fail_grade)
            ->first();

    }
}

if (! function_exists('is_optional_subject')) {
    function is_optional_subject($student_id, $subject_id): bool
    {
        try {
            $result = SmOptionalSubjectAssign::where('student_id', $student_id)->where('subject_id', $subject_id)->first();

            return (bool) $result;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('getMarksOfPart')) {
    function getMarksOfPart($student_id, $subject_id, $class_id, $section_id, $exam_term_id)
    {
        try {
            return SmMarkStore::where([
                ['student_id', $student_id],
                ['class_id', $class_id],
                ['subject_id', $subject_id],
                ['section_id', $section_id],
                ['exam_term_id', $exam_term_id],
            ])->get();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('getExamResult')) {
    function getExamResult($exam_id, $student)
    {
        $eligible_subjects = SmAssignSubject::where('class_id', $student->class_id)->where('section_id', $student->section_id)->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)->get();

        foreach ($eligible_subjects as $eligible_subject) {

            $getMark = SmResultStore::where([
                ['exam_type_id', $exam_id],
                ['class_id', $student->class_id],
                ['section_id', $student->section_id],
                ['student_id', $student->id],
                ['subject_id', $eligible_subject->subject_id],
            ])->first();

            if ($getMark == '') {
                return false;
            }

            return SmResultStore::where([
                ['exam_type_id', $exam_id],
                ['class_id', $student->class_id],
                ['section_id', $student->section_id],
                ['student_id', $student->id],
            ])->get();
        }

        return null;
    }
}

if (! function_exists('teacherAssignedClass')) {
    function teacherAssignedClass()
    {
        try {
            $class_id = [];
            $role_id = Auth::user()->role_id;
            if ($role_id == 4) {
                $classes = SmClassTeacher::where('teacher_id', Auth::user()->id)->get(['id']);
                foreach ($classes as $class) {
                    $class_id[] = $class->module_id;
                }
            } else {

                $general_setting = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();

                return @$general_setting->school_name;
            }
        } catch (Exception $exception) {
            return $class_id = [];
        }

        return null;
    }
}

if (! function_exists('getValueByStringTestRegistration')) {
    function getValueByStringTestRegistration(array $data, $str)
    {
        if ($str == 'password') {
            return '123456';
        }

        if ($str == 'school_name') {
            if (moduleStatusCheck('Saas') == true) {
                $student_info = SmStudentRegistration::find(@$data['id']);

                return @$student_info->school->school_name;
            }

            $general_setting = SmGeneralSettings::find(1);

            return @$general_setting->school_name;

        }

        if ($data['slug'] == 'student') {
            $student_info = SmStudentRegistration::find(@$data['id']);
            if ($str == 'name') {
                return @$student_info->first_name.' '.@$student_info->last_name;
            }

            if ($str == 'guardian_name') {
                return @$student_info->guardian_name;
            }

            if ($str == 'class') {
                return @$student_info->class->class_name;
            }

            if ($str == 'section') {
                return @$student_info->section->section_name;
            }
        } elseif ($data['slug'] == 'parent') {
            $parent_info = SmStudentRegistration::find(@$data['id']);
            if ($str == 'name') {
                return @$parent_info->guardian_name;
            }

            if ($str == 'student_name') {
                return @$parent_info->first_name.' '.@$parent_info->last_name;
            }
        }

        return null;
    }
}

if (! function_exists('getValueByStringTestReset')) {
    function getValueByStringTestReset(array $data, $str)
    {
        if ($str == 'school_name') {

            $general_setting = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();

            return @$general_setting->school_name;
        }

        if ($str == 'name') {
            $user = User::where('email', $data['email'])->first();

            return @$user->full_name;
        }

        return null;
    }
}

if (! function_exists('subjectPosition')) {
    /**
     * @return float[]
     */
    function subjectPosition($subject_id, $class_id, $custom_result): array
    {

        $students = SmStudent::where('class_id', $class_id)->get();

        $subject_mark_array = [];
        foreach ($students as $student) {
            $subject_marks = 0;

            $first_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id1)->sum('total_marks');

            $subject_marks += $first_exam_mark / 100 * $custom_result->percentage1;

            $second_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id2)->sum('total_marks');

            $subject_marks += $second_exam_mark / 100 * $custom_result->percentage2;

            $third_exam_mark = SmMarkStore::where('student_id', $student->id)->where('class_id', $class_id)->where('subject_id', $subject_id)->where('exam_term_id', $custom_result->exam_term_id3)->sum('total_marks');

            $subject_marks += $third_exam_mark / 100 * $custom_result->percentage3;

            $subject_mark_array[] = round($subject_marks);
        }

        arsort($subject_mark_array);

        return $subject_mark_array;
    }
}

if (! function_exists('getValueByStringDuesFees')) {
    function getValueByStringDuesFees($student_detail, $str, array $fees_info)
    {

        if ($str == 'student_name') {

            return @$student_detail->full_name;
        }

        if ($str == 'parent_name') {

            $parent_info = SmParent::find($student_detail->parent_id);

            return @$parent_info->fathers_name;
        }

        if ($str == 'due_amount') {

            return @$fees_info['dues_fees'];
        }

        if ($str == 'due_date') {

            $fees_master = SmFeesMaster::find($fees_info['fees_master']);

            return @$fees_master->date;
        }

        if ($str == 'school_name') {

            return @Auth::user()->school->school_name;
        }

        if ($str == 'fees_name') {

            $fees_master = SmFeesMaster::find($fees_info['fees_master']);

            return $fees_master->feesTypes->name;
        }

        return null;
    }
}

if (! function_exists('assignedRoutineSubject')) {

    function assignedRoutineSubject($class_id, $section_id, $exam_id, $subject_id)
    {

        try {
            return SmExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)->where('exam_term_id', $exam_id)->where('subject_id', $subject_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('assignedRoutine')) {

    function assignedRoutine($class_id, $section_id, $exam_id, $subject_id, $exam_period_id)
    {
        try {
            return SmExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)->where('exam_term_id', $exam_id)->where('subject_id', $subject_id)
                ->where('exam_period_id', $exam_period_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('is_absent_check')) {

    function is_absent_check($exam_id, $class_id, $section_id, $subject_id, $student_id)
    {
        try {
            $exam_attendance = SmExamAttendance::where('exam_id', $exam_id)->where('class_id', $class_id)->where('section_id', $section_id)->where('subject_id', $subject_id)->first();

            return SmExamAttendanceChild::where('exam_attendance_id', $exam_attendance->id)->where('student_id', $student_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('feesPayment')) {
    function feesPayment($type_id, $student_id)
    {
        try {
            return SmFeesPayment::where('active_status', 1)->where('fees_type_id', $type_id)->where('student_id', $student_id)->get();
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (! function_exists('generalSetting')) {
    function generalSetting()
    {
        if (session()->has('generalSetting')) {
            return session()->get('generalSetting');
        }

        if (app()->bound('school')) {
            $generalSetting = SmGeneralSettings::where('school_id', app('school')->id)->first();
        } elseif (request('school_id')) {
            $generalSetting = SmGeneralSettings::where('school_id', request('school_id'))->first();
        } else {
            // $generalSetting = Auth::check() ? SmGeneralSettings::where('school_id', Auth::user()->school_id)->first() : SmGeneralSettings::where('school_id',app('school')->id)->first();
            $generalSetting = Auth::check() ? SmGeneralSettings::where('school_id', Auth::user()->school_id)->first() : SmGeneralSettings::where('school_id', 1)->first();
        }

        session()->put('generalSetting', $generalSetting);

        return session()->get('generalSetting');
    }
}

if (! function_exists('systemDateFormat')) {
    function systemDateFormat()
    {
        if (session()->has('system_date_format')) {
            return session()->get('system_date_format');
        }

        $system_date_format = SmDateFormat::find(DB::table('sm_general_settings')->first()->date_format_id);
        session()->put('system_date_foramt', $system_date_format);

        return session()->get('system_date_foramt');

    }
}

if (! function_exists('emailTemplate')) {
    function emailTemplate()
    {
        if (session()->has('email_template')) {
            return session()->get('email_template');
        }

        $email_template = SmsTemplate::where('school_id', Auth::user()->school_id)->first();
        session()->put('email_template', $email_template);

        return session()->get('email_template');

    }
}

if (! function_exists('dashboardBackground')) {
    function dashboardBackground()
    {
        return app('dashboard_bg');
    }
}

if (! function_exists('allStyles')) {
    function allStyles()
    {

        if (session()->has('all_styles')) {
            return session()->get('all_styles');
        }

        $all_styles = SmStyle::where('school_id', 1)->where('active_status', 1)->get();
        session()->put('all_styles', $all_styles);

        return session()->get('all_styles');

    }
}

if (! function_exists('textDirection')) {
    function textDirection()
    {

        if (session()->has('text_direction')) {
            return session()->get('text_direction');
        }

        $ttl_rtl = Auth::user()->rtl_ltl;
        session()->put('text_direction', $ttl_rtl);

        // return $ttl_rtl;
        return session()->get('text_direction');

    }
}

if (! function_exists('userRtlLtl')) {
    function userRtlLtl()
    {
        // return 1;

        if (session()->has('user_text_direction')) {
            return session()->get('user_text_direction');
        }

        $school_id = app()->bound('school') ? app('school')->id : 1;
        $user = User::where('role_id', 1)->where('school_id', $school_id)->first();

        $ttl_rtl = $user ? $user->rtl_ltl : 2;
        session()->put('user_text_direction', $ttl_rtl);

        return session()->get('user_text_direction');

    }
}

if (! function_exists('userLanguage')) {
    function userLanguage()
    {

        if (session()->has('user_language')) {
            return session()->get('user_language');
        }

        $language = Auth::user()->language;
        session()->put('user_language', $language);

        return session()->get('user_language');

    }
}

if (! function_exists('schoolConfig')) {
    function schoolConfig()
    {
        return app('school_info');
    }
}

if (! function_exists('selectedLanguage')) {
    function selectedLanguage()
    {
        if (session()->has('selected_language')) {
            return session()->get('selected_language');
        }

        $selected_language = Auth::check() ? SmGeneralSettings::where('school_id', Auth::user()->school_id)->first() :
            DB::table('sm_general_settings')->where('school_id', 1)->first();
        session()->put('selected_language', $selected_language);

        return session()->get('selected_language');

    }
}

if (! function_exists('profile')) {
    function profile()
    {
        return auth()->user()->profile;
    }
}

if (! function_exists('getSession')) {
    function getSession()
    {
        if (session()->has('session')) {
            return session()->get('session');
        }

        $selected_language = Auth::check() ? SmGeneralSettings::where('school_id', Auth::user()->school_id)->first() :
            DB::table('sm_general_settings')->where('school_id', 1)->first();
        $session = DB::table('sm_academic_years')->where('id', $selected_language->session_id)->first();
        session()->put('session', $session);

        return session()->get('session');

    }
}

if (! function_exists('systemLanguage')) {
    function systemLanguage()
    {
        return Cache::rememberForever('system_language_'.auth()->user()->school_id, fn ()  => SmLanguage::where('school_id', auth()->user()->school_id)->select('language_universal', 'native')->get()
        );

    }
}

if (! function_exists('academicYears')) {
    function academicYears()
    {
        // session()->forget('academic_years');
        if (moduleStatusCheck('University')) {
            if (! session()->has('academic_years')) {
                $academic_years = Auth::check() ? UnAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
                session()->put('academic_years', $academic_years);

                return session()->get('academic_years');
            }

            return session()->get('academic_years');

        }

        if (session()->has('academic_years')) {
            return session()->get('academic_years');
        }

        $academic_years = Auth::check() ? SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';
        session()->put('academic_years', $academic_years);

        return session()->get('academic_years');

    }
}

if (!function_exists('getActiveSubjects')) {
    function getActiveSubjects()
    {
        return SmSubject::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->get();
    }
}

if (!function_exists('subjectFullMark')) {
    function subjectFullMark($examtype, $subject, $class_id = null, $section_id = null, $shift_id =null)
    {
        $school_id = 1;
        if (Auth::check()) {
            $school_id = Auth::user()->school_id;
        } elseif (app()->bound('school')) {
            $school_id = app('school')->id;
        }

        try {
            $full_mark = SmExam::withOutGlobalScopes()
                ->where('school_id', $school_id)
                ->where('exam_type_id', $examtype);

            if (moduleStatusCheck('University')) {
                $full_mark = $full_mark->where('un_subject_id', $subject);
                return round(optional($full_mark->first(['exam_mark']))->exam_mark ?? 0, 2);
            } else {
                $full_mark = $full_mark->where('subject_id', $subject)
                    ->where('class_id', $class_id);

                if (!is_null($section_id)) {
                    $full_mark = $full_mark->where('section_id', $section_id);
                }

                if (!is_null($shift_id)) {
                    $full_mark = $full_mark->where('shift_id', $shift_id);
                }

                return round(optional($full_mark->first(['exam_mark']))->exam_mark ?? 0, 2);
            }
        } catch (Exception $exception) {
            return 0;
        }
    }
}


if (! function_exists('subject100PercentMark')) {
    function subject100PercentMark(): int
    {
        try {
            return 100;
        } catch (Exception $exception) {
            return 0;
        }
    }
}

if (! function_exists('teacherAccess')) {
    function teacherAccess($user = null): bool
    {
        if(!$user){
            $user = auth()->user();
        }
        return $user?->role_id == 4;

    }
}

if (! function_exists('subjectPercentageMark')) {
    function subjectPercentageMark($obtained_mark, $full_nark)
    {
        if (! $full_nark) {
            return 0;
        }

        try {
            return round(($obtained_mark / $full_nark) * 100, 2);
        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('termWiseFullMark')) {
    function termWiseFullMark($type_ids, $student_id, $academic_id = null)
    {
        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            $average_gpa = 0;
            foreach ($type_ids as $type_id) {
                $total_gpa = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                if ($total_subject) {
                    $average_gpa += ($total_gpa / $total_subject) * ($percentage / 100);
                }
            }

            return $average_gpa;
        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('termWiseGpa')) {
    function termWiseGpa($type_id, $student_id, $with_optional_subject_mark = null, $academic_id = null)
    {
        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            $average_gpa = 0;
            if ($with_optional_subject_mark == null) {
                $total_gpa = SmResultStore::select('total_gpa_point')->where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = SmResultStore::select('subject_id')->where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                if ($total_subject) {
                    $average_gpa += ($total_gpa / $total_subject) * ($percentage / 100);
                }

                return $average_gpa;
            }

            if ($with_optional_subject_mark !== null) {

                $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('exam_percentage')->exam_percentage;

                return $average_gpa + $with_optional_subject_mark * ($percentage / 100);
            }
        } catch (Exception $exception) {
            return false;
        }

        return null;
    }
}

if (! function_exists('termWiseTotalMark')) {
    function termWiseTotalMark($type_id, $student_id, $optional_subject = null, $academic_id = null)
    {
        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            if ($optional_subject == null) {
                $average_gpa = 0;
                $total_gpa = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $total_subject = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('subject_id');

                if ($total_subject) {
                    $average_gpa += $total_gpa / $total_subject;
                }

                return $average_gpa;
            }

            if ($optional_subject !== null) {
                $average_gpa = 0;
                $optional_subject_extra_gpa = 0;

                $class_id = StudentRecord::find($student_id)->class_id;
                $optional_subject_above = SmClassOptionalSubject::where('class_id', $class_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', $academic_id)
                    ->first('gpa_above')->gpa_above;

                $subject_ids = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get('subject_id');

                $optional_subject_id = SmOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                    ->where('student_id', $student_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first('subject_id')->subject_id;

                $without_optional_subject_gpa = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', '!=', $optional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $optional_subject_gpa = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $optional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $maxgpa = SmMarksGrade::withOutGlobalScopes()->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->max('gpa');

                if ($optional_subject_gpa > $optional_subject_above) {
                    $optional_subject_extra_gpa = $optional_subject_gpa - $optional_subject_above;
                }

                $with_optional_subject_extra_gpa = $without_optional_subject_gpa + $optional_subject_extra_gpa;

                $final_gpa_with_optional_subject = $with_optional_subject_extra_gpa / (count($subject_ids) - 1);

                if ($maxgpa < $final_gpa_with_optional_subject) {
                    return $maxgpa;
                }

                return $final_gpa_with_optional_subject;

            }
        } catch (Exception $exception) {
            return false;
        }

        return null;
    }
}

if (! function_exists('optionalSubjectFullMark')) {
    function optionalSubjectFullMark($type_id, $student_id, $above_gpa, $purpose = null, $academic_id = null)
    {
        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            $subject_ids = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get('subject_id');

            $additional_subject_id = SmOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                ->where('record_id', $student_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('subject_id')->subject_id;

            if ($purpose == 'optional_sub_gpa') {
                return SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');
            }

            if ($purpose == 'with_optional_sub_gpa') {
                $total_mark = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->sum('total_gpa_point');

                $exam_type_id = SmResultStore::where('student_record_id', $student_id)
                    ->where('exam_type_id', $type_id)
                    ->where('subject_id', $additional_subject_id)
                    ->where('academic_id', $academic_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->count('exam_type_id');

                return ($total_mark - $above_gpa) * $exam_type_id;
            }
        } catch (Exception $exception) {
            return false;
        }

        return null;
    }
}

if (! function_exists('termWiseAddOptionalMark')) {
    function termWiseAddOptionalMark($type_id, $student_id, $above_gpa, $academic_id = null)
    {
        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            $subject_ids = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->get('subject_id');

            $additional_subject_id = SmOptionalSubjectAssign::whereIn('subject_id', $subject_ids)
                ->where('record_id', $student_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('subject_id')->subject_id;

            $additional_subject_mark = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->sum('total_gpa_point');

            $additional_single_subject_mark = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('total_gpa_point')->total_gpa_point;

            $additional_mark_reduction = $additional_single_subject_mark - $above_gpa;
            $all_subject_mark = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', '!=', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->sum('total_gpa_point');

            $without_additional_total_subject = SmResultStore::where('student_record_id', $student_id)
                ->where('exam_type_id', $type_id)
                ->where('subject_id', '!=', $additional_subject_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->count('subject_id');

            $with_additional_full_gpa = $all_subject_mark + ($additional_subject_mark - $above_gpa);

            $percentage = CustomResultSetting::where('exam_type_id', $type_id)
                ->where('academic_id', $academic_id)
                ->where('school_id', Auth::user()->school_id)
                ->first('exam_percentage')->exam_percentage;

            return ($with_additional_full_gpa / $without_additional_total_subject) * ($percentage / 100);
        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('gradeName')) {

    function gradeName($total_gpa, $academic_id = null)
    {
        $school_id = 1;
        if (Auth::check()) {
            $school_id = Auth::user()->school_id;
        } elseif (app()->bound('school')) {
            $school_id = app('school')->id;
        }

        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            return SmMarksGrade::where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->where('from', '<=', $total_gpa)
                ->where('up', '>=', $total_gpa)
                ->first('grade_name')->grade_name;
        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('remarks')) {
    function remarks($total_gpa, $academic_id = null)
    {
        $school_id = 1;
        if (Auth::check()) {
            $school_id = Auth::user()->school_id;
        } elseif (app()->bound('school')) {
            $school_id = app('school')->id;
        }

        if (! $academic_id) {
            $academic_id = getAcademicId();
        }

        try {
            return SmMarksGrade::where('academic_id', $academic_id)
                ->where('school_id', $school_id)
                ->where('from', '<=', $total_gpa)
                ->where('up', '>=', $total_gpa)
                ->first('description')->description;
        } catch (Exception $exception) {
            return false;
        }
    }
}

if (! function_exists('subjectHighestMark')) {
    function subjectHighestMark($exam_id, $subject_id, $class_id, $section_id)
    {
        $school_id = 1;
        if (Auth::check()) {
            $school_id = Auth::user()->school_id;
        } elseif (app()->bound('school')) {
            $school_id = app('school')->id;
        }

        try {
            $highest_mark = SmResultStore::where([['class_id', $class_id], ['exam_type_id', $exam_id], ['section_id', $section_id]])
                ->where('subject_id', $subject_id)
                ->where('school_id', $school_id)
                ->where('academic_id', getAcademicId())
                ->max('total_marks');

            return round($highest_mark, 2);
        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('getAllUserForChatBasedOnCondition')) {
    function getAllUserForChatBasedOnCondition()
    {
        try {
            $users = User::with('roles')->where('id', '!=', auth()->id())->get();
            if (app('general_settings')->get('chat_can_teacher_chat_with_parents') == 'no' && auth()->user()->roles->id == 4) {
                foreach ($users as $index => $user) {
                    if ($user->roles->id == 3) {
                        $users->forget($index);
                    }
                }
            }

            return $users;
        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('chatOpen')) {
    function chatOpen(): bool
    {
        return app('general_settings')->get('chat_open') == 'yes';
    }
}

// Jitsi Module Start
if (! function_exists('getDomainName')) {
    function getDomainName($url)
    {
        $url_domain = preg_replace('(^https?://)', '', $url);
        $url_domain = preg_replace('(^http?://)', '', $url_domain);

        return str_replace('/', '', $url_domain);
    }
}

// Jitsi Module End

if (! function_exists('invitationRequired')) {
    function invitationRequired(): bool
    {
        return app('general_settings')->get('chat_invitation_requirement') == 'required';
    }
}

if (! function_exists('intallMdouleMenu')) {
    function intallMdouleMenu($module_id, $module_name): bool
    {
        if (Auth::user()->role_id == 2 || Auth::user()->role_id == 3) {
            $menu_manage_module_id = MenuManage::where('active_status', 1)
                ->where('user_id', Auth::user()->id)
                ->where('role_id', Auth::user()->role_id)
                ->where('module_id', $module_id)
                ->first();
        } else {
            $menu_manage_module_id = MenuManage::where('active_status', 1)
                ->where('user_id', Auth::user()->id)
                ->where('role_id', Auth::user()->role_id)
                ->where('module_addons', $module_id)
                ->first();
        }

        return moduleStatusCheck($module_name) == true && is_null($menu_manage_module_id);

    }
}

if (! function_exists('customFieldValue')) {
    function customFieldValue($student_id, $labelName, $formName)
    {
        $custom_field_values = [];
        if ($formName == 'student_registration') {
            $custom_field_data = SmStudent::withOutGlobalScopes()->where('id', $student_id)->first();
            if (is_null($custom_field_data) && moduleStatusCheck('ParentRegistration')) {
                $custom_field_data = SmStudentRegistration::find($student_id);
            }

            @$value = $custom_field_data->custom_field;
        } elseif ($formName == 'staff_registration') {
            $custom_field_data = SmStaff::withOutGlobalScopes()->where('id', $student_id)->first();
            $value = $custom_field_data->custom_field;
        } elseif ($formName == 'school_registration') {
            $custom_field_data = SmSchool::withOutGlobalScopes()->where('id', $student_id)->first();
            $value = $custom_field_data->custom_field;
        } else {
            $value = null;
        }

        if ($value !== null) {
            $custom_field_values = json_decode($custom_field_data->custom_field, true);
            if (array_key_exists($labelName, $custom_field_values)) {
                return $custom_field_values[$labelName];
            }

            return null;

        }

        return null;
    }
}

if (! function_exists('paymentMethodName')) {
    function paymentMethodName($payment_method_id): bool
    {
        $paymentMethodName = SmPaymentMethhod::where('id', $payment_method_id)
            ->where('school_id', Auth::user()->school_id)
            ->first('method')->method;

        return $paymentMethodName == 'Bank';

    }
}

if (! function_exists('moduleVersion')) {
    function moduleVersion(string $module_name)
    {
        $dataPath = 'Modules/'.$module_name.'/'.$module_name.'.json';
        $strJsonFileContents = file_get_contents($dataPath);
        $array = json_decode($strJsonFileContents, true);

        return $array[$module_name]['versions'][0];
    }
}

if (! function_exists('menuPosition')) {
    function menuPosition($id)
    {

        $is_have = count(app('sidebar_news')) > 0;
        if ($id == 'is_submit') {
            return $is_have ? 1 : 0;
        }

        if ($is_have) {
            $sidebar = app('sidebar_news')->where('active_status', 1)->where('infix_module_id', $id)->first();

            return $sidebar ? $sidebar->parent_position_no : $id;
        }

        return false;

    }
}

if (! function_exists('menuStatus')) {
    function menuStatus($id)
    {
        $is_have = count(app('sidebar_news')) > 0;
        if (($is_have)) {
            $is_have_id = app('sidebar_news')->where('infix_module_id', $id)->first();
            if ($is_have_id) {
                return $is_have_id->active_status == 1;
            }

            return auth()->user()->role_id == 1 ? true : userPermission($id);

        }

        return true;
    }
}

if (! function_exists('courseSetting')) {
    function courseSetting()
    {
        return CourseSetting::where('school_id', Auth::user()->school_id)->first();
    }
}

if (! function_exists('fileUpload')) {
    function fileUpload($file, string $destination): string
    {

        $fileName = '';

        if (! $file) {
            return $fileName;
        }

        $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

        if (! file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $file->move($destination, $fileName);

        return $destination.$fileName;
    }
}

if (! function_exists('fileUpdate')) {
    function fileUpdate($databaseFile, $file, $destination)
    {

        $fileName = '';

        if ($file) {
            $fileName = fileUpload($file, $destination);

            if ($databaseFile && file_exists($databaseFile)) {

                unlink($databaseFile);
            }
        } elseif (! $file && $databaseFile) {
            $fileName = $databaseFile;
        }

        return $fileName;
    }
}

if (! function_exists('putEnvConfigration')) {
    function putEnvConfigration(string $envKey, string $envValue): bool
    {

        $value = '"'.$envValue.'"';
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $str .= "\n";
        $keyPosition = mb_strpos($str, $envKey.'=');

        if (is_bool($keyPosition)) {

            $str .= $envKey.'="'.$envValue.'"';
        } else {
            $endOfLinePosition = mb_strpos($str, "\n", $keyPosition);
            $oldLine = mb_substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
            $str = str_replace($oldLine, sprintf('%s=%s', $envKey, $value), $str);

            $str = mb_substr($str, 0, -1);
        }

        return (bool) file_put_contents($envFile, $str);

    }
}

if (! function_exists('feesInvoiceSettings')) {
    function feesInvoiceSettings()
    {
        return FmFeesInvoiceSettings::where('school_id', Auth::user()->school_id)->first();
    }
}

if (! function_exists('feesInvoiceNumber')) {
    function feesInvoiceNumber($invoice): string
    {
        $settings = feesInvoiceSettings();
        $positions = json_decode($settings->invoice_positions);
        $format = '';
        foreach ($positions as $position) {
            if ($format !== '' && $format !== '0') {
                $format .= '-';
            }

            $format .= $position->id;
        }

        $format .= '-inv_id';

        $key = [
            'prefix',
            'admission_no',
            'class',
            'section',
            'inv_id',
        ];

        $value = [
            $settings->prefix,
            Str::limit(@$invoice->studentInfo->admission_no, $settings->admission_limit),
            Str::limit(@$invoice->recordDetail->class->class_name, $settings->class_limit),
            Str::limit(@$invoice->recordDetail->section->section_name, $settings->section_limit),
            $settings->uniq_id_start + $invoice->id,
        ];

        return str_replace($key, $value, $format);
    }
}

if (! function_exists('send_sms')) {
    function send_sms(?string $reciver_number, $purpose, $data): void
    {
        if(!$reciver_number){
            return;
        }
        if ($purpose !== 'test_sms') {
            $templete = getTempleteDetails($purpose, 'sms');
            if (! $templete) {
                return;
            }
        }

        $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;

        $activeSmsGateway = SmSmsGateway::where('school_id', $school_id)->where('active_status', 1)->first();
        if (! $activeSmsGateway) {
            return;
        }

        if ($purpose !== 'test_sms') {
            $body = SmsTemplate::smsTempleteToBody($templete->body, $data);
        } else {
            $body = 'It is a Test Sms From '.$activeSmsGateway->gateway_name.' -'.generalSetting()->school_name;
        }

        try {
            if ($activeSmsGateway->gateway_name == 'Twilio') {
                $account_id = $activeSmsGateway->twilio_account_sid;
                $auth_token = $activeSmsGateway->twilio_authentication_token;
                $from_phone_number = $activeSmsGateway->twilio_registered_no;
                if (! $account_id || $auth_token) {
                    return;
                }

                $client = new Client($account_id, $auth_token);
                $result = $client->messages->create($reciver_number, ['from' => $from_phone_number, 'body' => $body]);
                $message = $result;
            } elseif ($activeSmsGateway->gateway_name == 'Msg91') {
                $msg91_authentication_key_sid = $activeSmsGateway->msg91_authentication_key_sid;
                $msg91_sender_id = $activeSmsGateway->msg91_sender_id;
                $msg91_route = $activeSmsGateway->msg91_route;
                $msg91_country_code = $activeSmsGateway->msg91_country_code;

                if ($reciver_number !== '') {
                    $curl = curl_init();
                    $url = 'https://api.msg91.com/api/sendhttp.php?mobiles='.
                        $reciver_number.'&authkey='.
                        $msg91_authentication_key_sid.'&route='.
                        $msg91_route.'&sender='.
                        $msg91_sender_id.'&message='.
                        urlencode($body).'&country='.$msg91_country_code;

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => 'GET', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                }
            } elseif ($activeSmsGateway->gateway_name == 'TextLocal') {
                // Config variables. Consult http://api.txtlocal.com/docs for more info.
                $url = $activeSmsGateway->type == 'in' ? 'https://api.textlocal.in/send/?' : 'https://api.txtlocal.com/send/?';
                $test = '0';
                $sender = $activeSmsGateway->textlocal_sender; // This is who the message appears to be from.
                $message = urlencode($body);
                $data = 'username='.$activeSmsGateway->textlocal_username.
                    '&hash='.$activeSmsGateway->textlocal_hash.
                    '&message='.$message.
                    '&sender='.$sender.
                    '&numbers='.$reciver_number.
                    '&test='.$test;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch); // This is the result from the API
                curl_close($ch);
            } elseif ($activeSmsGateway->gateway_name == 'AfricaTalking') {
                $username = $activeSmsGateway->africatalking_username;
                $apiKey = $activeSmsGateway->africatalking_api_key;
                $AT = new AfricasTalking($username, $apiKey);

                $sms_Send = $AT->sms();
                $sms_Send->send(['to' => $reciver_number, 'message' => $body]);
            } elseif ($activeSmsGateway->gateway_name == 'Himalayasms') {
                if ($reciver_number !== '') {
                    $client = new Http();
                    $request = $client->get('https://sms.techhimalaya.com/base/smsapi/index.php', [
                        'query' => [
                            'key' => $activeSmsGateway->himalayasms_key,
                            'senderid' => $activeSmsGateway->himalayasms_senderId,
                            'campaign' => $activeSmsGateway->himalayasms_campaign,
                            'routeid' => $activeSmsGateway->himalayasms_routeId,
                            'contacts' => $reciver_number,
                            'msg' => $body,
                            'type' => 'text',
                        ],
                        'http_errors' => false,
                    ]);
                    $request->getBody();
                }
            } elseif ($activeSmsGateway->gateway_type == 'custom') {
                send_custom_sms($reciver_number, $body, $activeSmsGateway);
            }
        } catch (Exception $exception) {
            Log::info($exception);
        }
    }
}

// time format 2 hours 30 min
if (! function_exists('timeCalculation')) {
    function timeCalculation($time): string
    {
        $minutes = floor(($time / (60)) % 60);
        $hours = floor(($time / (60 * 60)));

        $hours = ($hours < 10) ? '0'.$hours : $hours;

        $minutes = ($minutes < 10) ? '0'.$minutes : $minutes;
        if ($hours == 0) {
            return $minutes.' minutes ';
        }

        return $hours.' hours '.$minutes.' minutes ';
    }
}

function spn_active_link($route_or_path, string $class = 'active')
{

    if (is_array($route_or_path)) {
        foreach ($route_or_path as $route) {
            if (request()->is($route)) {
                return $class.' a';
            }
        }

        return in_array(request()->route()->getName(), $route_or_path) ? $class : false;
    }

    if (request()->route()->getName() == $route_or_path) {
        return $class.' b';
    }

    if (request()->is($route_or_path)) {
        return $class.' c';
    }

    return false;
}

function spn_nav_item_open($data, $default_class = 'active')
{
    foreach ($data as $d) {
        if (spn_active_link($d, true)) {
            return $default_class;
        }
    }

    return false;
}

if (! function_exists('addIncome')) {
    function addIncome($payment_method, $name, $amount, $fees_colection_id, $user_id, $request = null)
    {
        $payment_method = SmPaymentMethhod::where('method', $payment_method)->first();
        $income_head = generalSetting();

        $add_income = new SmAddIncome();
        $add_income->name = $name;
        $add_income->date = date('Y-m-d');
        $add_income->amount = $amount;
        $add_income->fees_collection_id = $fees_colection_id;
        $add_income->active_status = 1;
        $add_income->income_head_id = $income_head->income_head_id;
        $add_income->payment_method_id = $payment_method->id;
        $add_income->created_by = $user_id;
        $add_income->school_id = auth()->user()->school_id;
        if (moduleStatusCheck('University')) {
            $common = App::make(UnCommonRepositoryInterface::class);
            $common->storeUniversityData($add_income, $request);
            $add_income->un_academic_id = getAcademicId();
        } else {
            $add_income->academic_id = getAcademicId();
        }

        $add_income->academic_id = getAcademicId();

        return $add_income->save();

    }
}

if (! function_exists('sendNotification')) {
    function sendNotification($message, $url = null, $user_id = null, $role_id = null)
    {
        $notification = new SmNotification;
        $notification->date = date('Y-m-d');
        $notification->message = $message;
        $notification->url = $url;
        $notification->user_id = $user_id;
        $notification->role_id = $role_id;
        $notification->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $notification->un_academic_id = getAcademicId();
        } else {
            $notification->academic_id = getAcademicId();
        }

        return $notification->save();

    }
}

if (! function_exists('apk_secret')) {
    function apk_secret()
    {
        return Storage::exists('.apk_secret') ? Storage::get('.apk_secret') : false;
    }
}

function studentFieldLabel($fields, string $name)
{
    $field = $fields->where('field_name', $name)->first();
    if ($field && $field->label_name) {
        return $field->label_name;
    }

    return __('student.'.$name);
}

if (! function_exists('is_required')) {
    function is_required($field_name): bool
    {
        auth()->user()->school_id;
        $fields = getStudentRegistrationFields();
        $field = $fields->where('field_name', $field_name)
            ->first();

        return $field && $field->is_required == 1;
    }
}

if (! function_exists('is_show')) {
    function is_show($field_name): bool
    {
        $fields = getStudentRegistrationFields();
        $field = $fields->where('field_name', $field_name)->first();
        if (moduleStatusCheck('ParentRegistration')) {
            return $field && $field->is_show == 1;
        }

        return $field && $field->is_show == 1;

    }
}

if (! function_exists('getStudentRegistrationFields')) {
    function getStudentRegistrationFields($school_id = null)
    {

        if (! $school_id) {
            $school_id = auth()->user()->school_id;
        }

        return Cache::rememberForever('student_field_'.$school_id, function () use ($school_id) {
            return SmStudentRegistrationField::where('school_id', $school_id)->get()->filter(function ($field): bool {
                return ! $field->admin_section || isMenuAllowToShow($field->admin_section);
            });
        });
    }
}

if (! function_exists('has_permission')) {
    function has_permission($field_name): bool
    {

        $fields = getStudentRegistrationFields()
            ->when(auth()->user()->role_id == 2, function ($query): void {
                $query->where('student_edit', 1);
            })
            ->when(auth()->user()->role_id == 3, function ($query): void {
                $query->where('parent_edit', 1);
            })
            ->pluck('field_name')->toArray();

        return in_array($field_name, $fields);
    }
}

if (! function_exists('studentRecords')) {
    function studentRecords($request = null, $student_id = null, $school_id = null)
    {
        $builder = StudentRecord::query()->with('classes', 'studentDetail')->where('active_status', 1);
        if ($student_id !== null) {
            $builder->where('student_id', $student_id);
        }

        if ($school_id !== null) {
            $builder->where('school_id', $school_id);
        } else {
            $builder->where('school_id', auth()->user()->school_id);
        }

        if ($request !== null && ! moduleStatusCheck('University')) {
            $builder->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            })
                ->when($request->section, function ($query) use ($request): void {
                    $query->where('section_id', $request->section);
                });
        }

        if ($request !== null && moduleStatusCheck('University')) {
            $builder->when($request->un_session_id, function ($q) use ($request): void {
                $q->where('un_session_id', $request->un_session_id);
            })
                ->when($request->un_faculty_id, function ($q) use ($request): void {
                    $q->where('un_faculty_id', $request->un_faculty_id);
                })
                ->when($request->un_department_id, function ($q) use ($request): void {
                    $q->where('un_department_id', $request->un_department_id);
                })
                ->when($request->un_academic_id, function ($q) use ($request): void {
                    $q->where('un_academic_id', $request->un_academic_id);
                })
                ->when($request->un_semester_id, function ($q) use ($request): void {
                    $q->where('un_semester_id', $request->un_semester_id);
                })
                ->when($request->un_semester_label_id, function ($q) use ($request): void {
                    $q->where('un_semester_label_id', $request->un_semester_label_id);
                });
        }

        return $builder->when(moduleStatusCheck('University'), function ($q): void {
            $q->where('un_academic_id', getAcademicId());
        }, function ($query): void {
            $query->where('academic_id', getAcademicId());
        })->where('is_promote', 0);
    }
}

if (! function_exists('SchoolModuleStatus')) {
    function SchoolModuleStatus($schoolmodule): bool
    {
        return isModuleForSchool($schoolmodule);
    }
}

if (! function_exists('universityColumns')) {
    function universityColumns($table): void
    {
        $columns = [
            'un_sessions' => 'un_session_id',
            'un_faculties' => 'un_faculty_id',
            'un_departments' => 'un_department_id',
            'un_academic_years' => 'un_academic_id',
            'un_semesters' => 'un_semester_id',
            'un_semester_labels' => 'un_semester_label_id',
        ];
        foreach ($columns as $key => $column) {
            if (! Schema::hasColumn($table, $column)) {
                $table->unsignedBigInteger($column)->nullable();
            }

            if (Schema::hasTable($key)) {
                $table->foreign($column)->on($key)->references('id')->cascadeOnDelete();
            }
        }
    }
}

if (! function_exists('labelWiseStudentResult')) {
    /**
     * @return mixed[]
     */
    function labelWiseStudentResult($studentRecord, $subject_id, $examTerm = null): array
    {
        // $subejcts = [1,2,3,4,5];
        // $data = [];
        // foreach($subejcts as $subject) {
        //     $data[$subject]= [
        //         'result' => '',
        //         'total_mark' =>''
        //     ];
        // }
        // $assingSubjects = $studentRecord->unStudentSubjects->pluck('un_subject_id')->toArray();
        $marks = SmMarkStore::withOutGlobalScope(AcademicSchoolScope::class)->where('student_record_id', $studentRecord->id)
            ->where('un_semester_label_id', $studentRecord->un_semester_label_id)
            ->where('un_academic_id', $studentRecord->un_academic_id)
            // ->where('un_subject_id', $subject_id)
            ->where('school_id', auth()->user()->school_id);

        $exit = $marks->get();

        $data = [];
        $data['exit'] = $exit;
        $data['passSubject'] = [];
        $data['total_mark'] = null;
        $data['result'] = 'not taken';
        if (count($exit) > 0) {
            $data['result'] = 'fail';
            $settings = CustomResultSetting::where('school_id', $studentRecord->school_id)
                ->where('un_academic_id', $studentRecord->un_academic_id)
                ->whereNotIn('exam_type_id', [0])
                ->get();
            $subjectPassMark = Modules\University\Entities\UnSubject::where('id', $subject_id)
                ->where('school_id', $studentRecord->school_id)
                ->value('pass_mark');

            if (! $subjectPassMark) {
                $data['result'] = 'pass';
                $data['passSubject'] = [$subject_id];

                return $data;
            }

            if ($settings) {
                $total_mark = 0;
                foreach ($settings as $setting) {
                    $mark = $marks->where('exam_term_id', $setting->exam_type_id)->value('total_marks');
                    $total_mark += ($mark * $setting->exam_percentage) / 100;
                }

                if ($total_mark >= $subjectPassMark) {
                    $data['result'] = 'pass';
                    $data['passSubject'] = [$subject_id];
                }

                $data['total_mark'] = $total_mark;
            } else {
                $totalSubjectMark = $marks->count('total_marks');
                if ($totalSubjectMark >= $subjectPassMark) {
                    $data['result'] = 'pass';
                    $data['passSubject'] = [$subject_id];
                }

                $data['total_mark'] = $totalSubjectMark;
            }
        }

        return $data;
    }
}

const WEEK_DAYS = [
    3 => 1,
    4 => 2,
    5 => 3,
    6 => 4,
    7 => 5,
    1 => 6,
    2 => 0,
];

const WEEK_DAYS_BY_NAME = [
    'Saturday' => 6,
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
];

const PERMITTED_MODULE = [
    // keep it all lower case.
    'lead', 'lms', 'university', 'alumni',
];

if (! function_exists('directFees')) {
    function directFees(): bool
    {
        return (bool) generalSetting()->direct_fees_assign;
    }
}

if (! function_exists('discountFees')) {
    function discountFees($installment_id)
    {
        $amount = 0;
        $installment = DirectFeesInstallmentAssign::find($installment_id);
        if ($installment) {
            return $installment->amount - $installment->discount_amount;
        }

        return $amount;
    }
}

if (! function_exists('discount_fees')) {
    function discount_fees($amount, $discount = 0)
    {
        if ($discount) {
            return $amount - $discount;
        }
        return $amount;

    }
}

if (! function_exists('sm_fees_invoice')) {
    function sm_fees_invoice($invoice, $setting): string
    {
        $number = (($setting->start_form + $invoice) - 1);
        $format = $setting->prefix.'-'.$number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $setting->prefix,
            $setting->start_form,
        ];

        return str_replace($key, $value, $format);
    }
}

if (! function_exists('smFeesInvoice')) {
    function smFeesInvoice($invoice): string
    {
        $settings = FeesInvoice::where('school_id', auth()->user()->school_id)->first();

        $number = (($settings->start_form + $invoice) - 1);
        $format = $settings->prefix.'-'.$number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $settings->prefix,
            $settings->start_form,
        ];

        return str_replace($key, $value, $format);
    }
}

if (! function_exists('fees_payment_status')) {
    function fees_payment_status($amount, $discount = 0, $paid_amo = 0, $status = null): array
    {
        $balance_fees = ($amount - $discount) - ($paid_amo);
        if (moduleStatusCheck('University')) {
            if ($status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');

                return [$paid, 'bg-success'];
            }

            if ($status == 2 || ($paid_amo > 0)) {
                $partial = __('fees.partial');

                return [$partial, 'bg-warning'];
            }

            $unpaid = __('fees.unpaid');

            return [$unpaid, 'bg-danger'];

        }

        if ($status == 1 && $balance_fees == 0) {
            $paid = __('fees.paid');

            return [$paid, 'bg-success'];
        }

        if ($status == 2 || ($paid_amo > 0)) {
            $partial = __('fees.partial');

            return [$partial, 'bg-warning'];
        }

        $unpaid = __('fees.unpaid');

        return [$unpaid, 'bg-danger'];

    }
}

if (! function_exists('feesPaymentStatus')) {
    function feesPaymentStatus($installment_id): array
    {
        if (moduleStatusCheck('University')) {
            $feesInstallment = UnFeesInstallmentAssign::find($installment_id);
            $balance_fees = discountFeesAmount($feesInstallment->id) - ($feesInstallment->paid_amount);
            if ($feesInstallment->active_status == 1 && $balance_fees == 0) {
                $paid = __('fees.paid');

                return [$paid, 'bg-success'];
            }

            if ($feesInstallment->active_status == 2 || ($feesInstallment->paid_amount > 0)) {
                $partial = __('fees.partial');

                return [$partial, 'bg-warning'];
            }

            $unpaid = __('fees.unpaid');

            return [$unpaid, 'bg-danger'];

        }

        $feesInstallment = DirectFeesInstallmentAssign::find($installment_id);
        $balance_fees = discount_fees($feesInstallment->amount, $feesInstallment->discount_amount) - ($feesInstallment->paid_amount);
        if ($feesInstallment->active_status == 1 && $balance_fees == 0) {
            $paid = __('fees.paid');

            return [$paid, 'bg-success'];
        }

        if ($feesInstallment->active_status == 2 || ($feesInstallment->paid_amount > 0)) {
            $partial = __('fees.partial');

            return [$partial, 'bg-warning'];
        }

        $unpaid = __('fees.unpaid');

        return [$unpaid, 'bg-danger'];

    }
}

if (! function_exists('universityFeesInvoice')) {
    function universityFeesInvoice($invoice): string
    {
        $settings = FeesInvoice::where('school_id', auth()->user()->school_id)
            ->first();

        $number = $settings->start_form + $invoice;
        $format = $settings->prefix.'-'.$number;

        $key = [
            'prefix',
            'start_form',
        ];

        $value = [
            $settings->prefix,
            $settings->start_form,
        ];

        return str_replace($key, $value, $format);
    }
}

if (! function_exists('smPaymentRemainder')) {
    function smPaymentRemainder($school_id = null): ?bool
    {
        $today = date('Y-m-d');

        if (! $school_id) {
            $school_id = auth()->user()->school_id;
        }

        $notificationData = DirectFeesReminder::where('school_id', $school_id)
            ->first();
        $notificationType = json_decode($notificationData->notification_types);

        $dueDate = Carbon::parse($today)->addDays($notificationData->due_date_before)->format('Y-m-d');

        $feesDues = DirectFeesInstallmentAssign::where('school_id', $school_id)
            ->where('active_status', '!=', 1)
            ->where('due_date', $dueDate)
            ->get();

        foreach ($feesDues as $feeDue) {
            if (in_array('system', $notificationType)) {
                $message = 'Fees Remainder';
                $user_id = @$feeDue->recordDetail->student->user_id;
                $role_id = @$feeDue->recordDetail->student->role_id;
                sendNotification($message, '', $user_id, $role_id);
            }

            if (in_array('email', $notificationType)) {
                $reciver_email = @$feeDue->recordDetail->student->email;
                $receiver_name = @$feeDue->recordDetail->student->full_name;
                $purpose = 'university_fees_remainder';

                $data['student_name'] = @$feeDue->recordDetail->student->full_name;
                $data['class'] = @$feeDue->recordDetail->class->class_name;
                $data['section'] = @$feeDue->recordDetail->section->section_name;
                $data['semester_label'] = @$feeDue->recordDetail->unSemesterLabel->name;
                $data['academic'] = @$feeDue->recordDetail->academic->name;
                $data['fees_type'] = @$feeDue->feesType->name;
                $data['amount'] = $feeDue->amount;
                $data['due_date'] = dateConvert($feeDue->due_date);
                send_mail($reciver_email, $receiver_name, $purpose, $data);
            }

            if (in_array('sms', $notificationType)) {
                $reciver_number = @$feeDue->recordDetail->student->mobile;
                $purpose = 'university_fees_remainder';
                $data['student_name'] = @$feeDue->recordDetail->student->full_name;
                $data['class'] = @$feeDue->recordDetail->class->class_name;
                $data['section'] = @$feeDue->recordDetail->section->section_name;
                $data['semester_label'] = @$feeDue->recordDetail->unSemesterLabel->name;
                $data['academic'] = @$feeDue->recordDetail->academic->name;
                $data['fees_type'] = @$feeDue->feesType->name;
                $data['amount'] = $feeDue->amount;
                $data['due_date'] = dateConvert($feeDue->due_date);
                send_sms($reciver_number, $purpose, $data);
            }

            return true;
        }

        return null;
    }
}

if (! function_exists('singleSubjectMark')) {
    function singleSubjectMark($record_id, $subject_id, $exam_id, $exam_rule = null)
    {
        try {
            $mark = 0;
            $full_mark = 100;

            if (moduleStatusCheck('University')) {
                $sm_mark = SmResultStore::where('student_record_id', $record_id)->where('un_subject_id', $subject_id)->where('exam_type_id', $exam_id)->first();
                if ($sm_mark) {
                    $full_mark = SmExam::where('exam_type_id', $exam_id)->where('un_subject_id', $subject_id)->where('un_semester_label_id', $sm_mark->un_semester_label_id)->where('un_section_id', $sm_mark->un_section_id)->first('exam_mark');
                }
            } else {
                $sm_mark = SmResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_id)->first();
                if ($sm_mark) {
                    $full_mark = SmExam::where('exam_type_id', $exam_id)->where('subject_id', $subject_id)->where('class_id', $sm_mark->class_id)->where('section_id', $sm_mark->section_id)->first('exam_mark');
                }
            }

            $mark = is_null($exam_rule) ? ($sm_mark->total_marks * 100) / $full_mark->exam_mark : $sm_mark->total_marks;

            return [round($mark, 2)];
        } catch (Exception $exception) {
            return [0];
        }
    }
}

if (! function_exists('subjectAverageMark')) {
    function subjectAverageMark($record_id, $subject_id)
    {
        try {
            $total_mark = 0;
            $grade = '';
            if (moduleStatusCheck('University')) {
                $result_setting = CustomResultSetting::where('un_academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)->get();
            } else {
                $result_setting = CustomResultSetting::where('academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)->get();
            }

            if ($result_setting) {
                foreach ($result_setting as $exam) {
                    $mark = SmResultStore::query();
                    $mark->where('student_record_id', $record_id)->where('exam_type_id', $exam->exam_type_id);
                    if (moduleStatusCheck('University')) {
                        $mark = $mark->where('un_subject_id', $subject_id);
                    } else {
                        $mark = $mark->where('subject_id', $subject_id);
                    }

                    $mark = $mark->first();

                    if ($mark) {
                        $full_mark = SmExam::query();
                        $full_mark->where('exam_type_id', $mark->exam_type_id);
                        if (moduleStatusCheck('University')) {
                            $full_mark = $full_mark->where('un_subject_id', $subject_id)
                                ->where('un_semester_label_id', $mark->un_semester_label_id)
                                ->where('un_section_id', $mark->un_section_id);
                        } else {
                            $full_mark->where('subject_id', $subject_id)
                                ->where('class_id', $mark->class_id)
                                ->where('section_id', $mark->section_id);
                        }

                        $full_mark = $full_mark->first('exam_mark');
                        $total_mark += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam->exam_percentage / 100));
                    }
                }
            } else {
                foreach (examTypes() as $exam) {
                    $mark = SmResultStore::query();
                    $mark->where('student_record_id', $record_id)->where('exam_type_id', $exam->id);
                    if (moduleStatusCheck('University')) {
                        $mark = $mark->where('un_subject_id', $subject_id);
                    } else {
                        $mark = $mark->where('subject_id', $subject_id);
                    }

                    $mark = $mark->first();

                    if ($mark) {
                        $full_mark = SmExam::query();
                        $full_mark->where('exam_type_id', $mark->exam_type_id);
                        if (moduleStatusCheck('University')) {
                            $full_mark = $full_mark->where('un_subject_id', $subject_id)
                                ->where('un_semester_label_id', $mark->un_semester_label_id)
                                ->where('un_section_id', $mark->un_section_id);
                        } else {
                            $full_mark->where('subject_id', $subject_id)
                                ->where('class_id', $mark->class_id)
                                ->where('section_id', $mark->section_id);
                        }

                        $full_mark = $full_mark->first('exam_mark');
                        $total_mark += $mark->total_marks;
                    }
                }
            }

            $total_mark = number_format($total_mark, 2);

            return [$total_mark];
        } catch (Exception $exception) {
            return [0];
        }
    }
}

if (! function_exists('allSubjectAverageMark')) {
    function allSubjectAverageMark($record_id, $subject_id)
    {
        try {
            $exam_rules = CustomResultSetting::where('school_id', Auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();
            $total_mark = 0;
            $grade = '';
            if (! is_null($exam_rules)) {
                foreach ($exam_rules as $exam_rule) {
                    $mark = SmResultStore::where('student_record_id', $record_id)->where('subject_id', $subject_id)->where('exam_type_id', $exam_rule->exam_type_id)->first();
                    if ($mark) {
                        $full_mark = SmExam::where('exam_type_id', $mark->exam_type_id)->where('subject_id', $subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                        $total_mark += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam_rule->exam_percentage / 100));
                    }
                }
            }

            $total_mark = number_format($total_mark, 2);

            return [$total_mark];
        } catch (Exception $exception) {
            return [0];
        }
    }

    if (! function_exists('allExamSubjectMark')) {
        function allExamSubjectMark($record_id, $exam_rule_id, $exam_rule = true)
        {
            try {
                $avg_marks = 0;
                $studentRecord = StudentRecord::find($record_id);

                if ($exam_rule) {
                    $exam_rule = CustomResultSetting::find($exam_rule_id);
                    if ($exam_rule) {
                        $result = SmResultStore::where('student_record_id', $record_id)
                            ->where('exam_type_id', $exam_rule->exam_type_id)
                            ->where('academic_id', getAcademicId())->get();
                        if ($result->count()) {
                            $total_marks = $result->sum('total_marks');
                            $avg_marks = ($total_marks / count($result)) * ($exam_rule->exam_percentage / 100);
                        }
                    }
                } else {
                    $result = SmResultStore::where('student_record_id', $record_id)
                        ->where('exam_type_id', $exam_rule_id)
                        ->where('academic_id', getAcademicId())->get();
                    if ($result->count()) {
                        $total_marks = $result->sum('total_marks');
                        $avg_marks = $total_marks / count($result);
                    }
                }

                $avg_marks = number_format($avg_marks, 2);

                return [$avg_marks];
            } catch (Exception $exception) {
                return [0];
            }
        }
    }

    if (! function_exists('allExamSubjectMarkAverage')) {
        function allExamSubjectMarkAverage($record_id, $all_subject_ids)
        {
            try {
                $total_avg = 0;
                if (moduleStatusCheck('University')) {
                    $exam_rules = CustomResultSetting::where('un_academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)
                        ->where('un_academic_id', getAcademicId())
                        ->get();
                } else {
                    $exam_rules = CustomResultSetting::where('academic_id', getAcademicId())->where('school_id', Auth()->user()->school_id)
                        ->where('academic_id', getAcademicId())
                        ->get();
                }

                if (count($exam_rules) > 0) {
                    foreach ($all_subject_ids as $all_subject_id) {
                        foreach ($exam_rules as $exam_rule) {
                            if (moduleStatusCheck('University')) {
                                $mark = SmResultStore::where('student_record_id', $record_id)->where('un_subject_id', $all_subject_id)->where('exam_type_id', $exam_rule->exam_type_id)->first();
                                $full_mark = SmExam::where('exam_type_id', $mark->exam_type_id)->where('un_subject_id', $all_subject_id)->where('class_id', $mark->class_id)->where('un_section_id', $mark->un_section_id)->first('exam_mark');
                            } else {
                                $mark = SmResultStore::where('student_record_id', $record_id)->where('subject_id', $all_subject_id)->where('exam_type_id', $exam_rule->exam_type_id)->first();
                                $full_mark = SmExam::where('exam_type_id', $mark->exam_type_id)->where('subject_id', $all_subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                            }

                            if ($mark) {
                                $total_avg += ((($mark->total_marks * 100) / $full_mark->exam_mark) * ($exam_rule->exam_percentage / 100));
                            }
                        }
                    }
                } else {
                    foreach ($all_subject_ids as $all_subject_id) {
                        foreach (examTypes() as $exam) {
                            if (moduleStatusCheck('University')) {
                                $mark = SmResultStore::where('student_record_id', $record_id)->where('un_subject_id', $all_subject_id)->where('exam_type_id', $exam->id)->first();
                            } else {
                                $mark = SmResultStore::where('student_record_id', $record_id)->where('subject_id', $all_subject_id)->where('exam_type_id', $exam->id)->first();
                            }

                            if ($mark) {
                                if (moduleStatusCheck('University')) {
                                    $full_mark = SmExam::where('exam_type_id', $mark->id)->where('un_subject_id', $all_subject_id)->where('un_semester_label_id', $mark->un_semester_label_id)->where('un_section_id', $mark->un_section_id)->first('exam_mark');
                                } else {
                                    $full_mark = SmExam::where('exam_type_id', $mark->id)->where('subject_id', $all_subject_id)->where('class_id', $mark->class_id)->where('section_id', $mark->section_id)->first('exam_mark');
                                }

                                $total_avg += $mark->total_marks;
                            }
                        }
                    }
                }

                if (count($all_subject_ids) > 0) {
                    $average = $total_avg > 0 ?  $total_avg / count($all_subject_ids):0;
                    return number_format($average, 2);
                }

                return number_format(0, 2);

            } catch (Exception $exception) {
                return 0;
            }
        }
    }

    if (! function_exists('avgSubjectPassMark')) {
        function avgSubjectPassMark($all_subject_ids)
        {
            try {
                $pass_mark = 0;
                $subjects = SmSubject::whereIn('id', $all_subject_ids)->get();
                if (count($subjects) > 0) {

                    $pass_mark = $subjects->sum('pass_mark') / count($subjects);
                }

                return number_format($pass_mark, 2);
            } catch (Exception $exception) {
                return 0;
            }
        }
    }
}

if (! function_exists('getSussSchools')) {
    function getSussSchools()
    {
        Cache::remember('saasSchools', 120, function (): void {
            $schools = SmSchool::get();
        });

        return Cache::get('saasSchools');
    }
}

if (! function_exists('calandarSettingByMenuName')) {
    function calandarSettingByMenuName($menu_name)
    {
        return Cache::rememberForever('calendarSetting', function () use ($menu_name) {
            return SmCalendarSetting::where('menu_name', $menu_name)
                ->select('font_color', 'bg_color', 'status')
                ->first();
        });
    }
}
if (!function_exists('shifts')) {
    function shifts()
    {
        try {
           $shifts = Cache::rememberForever('shifts', function () {
                return Shift::where('active_status', 1)->where('school_id',auth()->user()->school_id)->get();
            });
            return $shifts;
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('classes')) {
    function classes()
    {
        try {
            $classes = Cache::rememberForever('classes', function () {
                return SmClass::where('active_status', 1)
                    ->where('school_id', auth()->user()->school_id)
                    ->get();
            });
            return $classes;
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (! function_exists('SaasSchool')) {
    function SaasSchool()
    {
        if (app()->bound('school')) {
            return app('school');
        }

        $request = request();
        $domain = $request->subdomain;
        $host = $request->getHttpHost();
        $school = null;
        $short_url = preg_replace('#^https?://#', '', rtrim(env('APP_URL', 'http://localhost'), '/'));
        if (! $domain) {
            $domain = str_replace('.'.$short_url, '', $host);
        }

        if ($domain == $host) {
            $domain = null;
        }

        $saas_module = 'Modules/Saas/Providers/SaasServiceProvider.php';
        if (file_exists($saas_module)) {
            $module_status = json_decode(file_get_contents('modules_statuses.json'), true);
            if (isset($module_status['Saas']) && $module_status['Saas']) {

                if ($domain) {
                    $school = SmSchool::where(['domain' => $domain, 'active_status' => 1])->firstOrFail();
                    $request->route()->forgetParameter('subdomain');
                } elseif ($host == $short_url) {
                    $school = SmSchool::where('id', 1)->first(); // \App\SmSchool::findOrFail(1);
                } elseif ($host !== $short_url && config('app.allow_custom_domain')) {
                    $school = SmSchool::where('custom_domain', $host)->where('active_status', 1)->first(); // \App\SmSchool::where(['custom_domain' => $host, 'active_status' => 1])->firstOrFail();
                } elseif (Auth::check()) {
                    $school = SmSchool::where('id', Auth::user()->school_id)->first();
                    SmSchool::findOrFail(Auth::user()->school_id);
                } else {
                    $school = SmSchool::where(['domain' => $domain, 'active_status' => 1])->firstOrFail();
                    $request->route()->forgetParameter('subdomain');
                }
            }
        }

        if (! $school) {
            $school = Auth::check() ? auth()->user()->school : SmSchool::where('id', 1)->first();
        }

        app()->forgetInstance('school');
        app()->instance('school', $school);

        return $school;
    }
}

if (! function_exists('SaasDomain')) {
    function SaasDomain()
    {
        return SaasSchool()->domain;
    }
}

if (! function_exists('saasEnv')) {
    function saasEnv($value, $default = null)
    {

        try {

            $domain = SaasDomain();
            $settings_prefix = Str::lower(str_replace(' ', '_', $domain));
            $path = storage_path('app/chat/'.$settings_prefix.'_settings.json');
            if (! file_exists($path)) {
                copy(storage_path('app/chat/default_settings.json'), $path);
            }

            $data = json_decode(file_get_contents($path), true);

            $settings = [];
            if (! empty($data)) {
                foreach ($data as $key => $property) {
                    $settings[$key] = $property;
                }
            }

            $env = $settings[$value] ?? '';
        } catch (Throwable $throwable) {
            $env = null;
        }

        if (empty($env)) {
            return $default;
        }

        return $env;
    }
}

if (! function_exists('examTypes')) {
    function examTypes()
    {
        try {
            return SmExamType::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('active_status', 1)
                ->get();
        } catch (Throwable $throwable) {
            return [];
        }
    }
}

if (! function_exists('allExamsSubjectTotalMark')) {
    function allExamsSubjectTotalMark($subject_id)
    {
        try {
            $toal_mark = 0;
            foreach (examTypes() as $exam) {
                $toal_mark += subjectFullMark($exam->id, $subject_id);
            }

            return $toal_mark;
        } catch (Throwable $throwable) {
            return 100;
        }
    }
}

function filterHeaderItems(array $items, string $keyword = 'header'): array {
    $header = [];
    foreach ($items as $key => $item) {
        if (stripos($key, $keyword . ':') == 0) {
            unset($items[$key]);
            $key = str($key)->replace($keyword.':', '')->trim(' ')->value();
            $header[$key] = $item;
        }
    }

    return [
        'header' => $header,
        'body' => $items
    ];
}

if (! function_exists('send_custom_sms')) {
    function send_custom_sms($reciver_number, $message, $active_gateway = null)
    {
        if (! $active_gateway) {
            $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;
            $active_gateway = SmSmsGateway::where('active_status', 1)->where('school_id', $school_id)->first();
        }

        if (! $active_gateway) {
            return null;
        }

        $sms_settings = CustomSmsSetting::where('gateway_id', $active_gateway->id)->first();

        $response = false;
        if (empty($sms_settings->gateway_url)) {
            Toastr::info(__('common.set_sms_credentials'), __('common.info'));

            return $response;
        }

        $request_data = [
            $sms_settings->send_to_parameter_name => $reciver_number,
            $sms_settings->messege_to_parameter_name => $message,
        ];

        if (! empty($sms_settings->param_key_1)) {
            $request_data[$sms_settings->param_key_1] = $sms_settings->param_value_1;
        }

        if (! empty($sms_settings->param_key_2)) {
            $request_data[$sms_settings->param_key_2] = $sms_settings->param_value_2;
        }

        if (! empty($sms_settings->param_key_3)) {
            $request_data[$sms_settings->param_key_3] = $sms_settings->param_value_3;
        }

        if (! empty($sms_settings->param_key_4)) {
            $request_data[$sms_settings->param_key_4] = $sms_settings->param_value_4;
        }

        if (! empty($sms_settings->param_key_5)) {
            $request_data[$sms_settings->param_key_5] = $sms_settings->param_value_5;
        }

        if (! empty($sms_settings->param_key_6)) {
            $request_data[$sms_settings->param_key_6] = $sms_settings->param_value_6;
        }

        if (! empty($sms_settings->param_key_7)) {
            $request_data[$sms_settings->param_key_7] = $sms_settings->param_value_7;
        }

        if (! empty($sms_settings->param_key_8)) {
            $request_data[$sms_settings->param_key_8] = $sms_settings->param_value_8;
        }

        $params = [];
        

        $formatted = filterHeaderItems($request_data);
       $params['headers'] = gv($formatted, 'header', []);
       $request_data = gv($formatted, 'body', []);


        $user_name = array_search('username', $sms_settings->toArray(), true);
        $password = array_search('password', $sms_settings->toArray(), true);
        $authorization = array_search('authorization', $sms_settings->toArray(), true);

        if ($sms_settings->set_auth == 'header') {
            if($user_name && $password){
                $params['auth'] = [
                    $request_data[$sms_settings->$user_name],
                    $request_data[$sms_settings->$password],
                ];
                unset($request_data['username']);
                unset($request_data['password']);
            }
            if($authorization){
                $params['headers'] = [
                  'authorization' => $request_data[$sms_settings->$user_name]
                ];
            }



        }

        if (array_key_exists('csms_id', $request_data)) {
            $request_data->csms_id = date('dmY');
        }

        $params['form_params'] = $request_data;


        $client = new Client();
        $method = mb_strtolower($sms_settings->request_method);

        if ($method == 'get') {
            return $client->$method($sms_settings->gateway_url.'?'.http_build_query($request_data));
        }

        return $client->post($sms_settings->gateway_url, $params);
    }
}

if (! function_exists('get_mobile_sms_data')) {
    function get_mobile_sms_data()
    {
        $school_id = Auth::check() && saasSettings('sms_settings') ? Auth::user()->school_id : 1;

        return SmSmsGateway::where('active_status', 1)->where('gateway_name', 'Mobile SMS')->where('school_id', $school_id)->first();
    }
}

// if (! function_exists('total_no_records')) {
//     function total_no_records($class_id, $section_id = null)
//     {
//         try {
//             $records = StudentRecord::query();
//             $records->where('class_id', $class_id)->where('is_promote', 0);
//             if ($section_id) {
//                 $records->where('section_id', $section_id);
//             }

//             return $records->whereHas('student')->count();
//         } catch (Throwable $throwable) {
//             return 0;
//         }
//     }
// }

function total_no_records($class_id, $section_id = null, $shift_id = null)
{
    $query = StudentRecord::query();

    $query->where('class_id', $class_id);

    if ($section_id) {
        $query->where('section_id', $section_id);
    }

    if ($shift_id) {
        $query->where('shift_id', $shift_id); 
    }

    return $query->count();
}

if (! function_exists('isSkip')) {
    function isSkip($name): bool
    {
        $data = \App\Models\ExamStepSkip::where('name', $name)->where('school_id', auth()->user()->school_id)->first();

        return (bool) $data;
    }
}

if (! function_exists('resultPrintStatus')) {
    function resultPrintStatus($data): bool
    {
        try {
            $printSettings = CustomResultSetting::first();
            if ($data == 'image') {
                return $printSettings->profile_image == $data;
            }
            if ($data == 'header') {
                return $printSettings->header_background == $data;
            }
            if ($data == 'body') {
                return $printSettings->body_background == $data;
            }
            if ($data == 'vertical_boarder') {
                return $printSettings->vertical_boarder == $data;
            }

            return false;

        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('getStudentMeritPosition')) {
    function getStudentMeritPosition($class_id, $section_id, $exam_term_id, $record_id)
    {
        try {
            $position = ExamMeritPosition::withOutGlobalScopes()->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_term_id)
                ->where('record_id', $record_id)
                ->first();
            if ($position) {
                return $position->position;
            }

            return '';

        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('getStudentMeritPosition')) {
    function getStudentMeritPosition($class_id, $section_id, $exam_term_id, $record_id)
    {
        try {
            $query = ExamMeritPosition::withOutGlobalScopes()
                ->where('class_id', $class_id)
                ->where('exam_term_id', $exam_term_id)
                ->where('record_id', $record_id);

            if (!is_null($section_id)) {
                $query->where('section_id', $section_id);
            }

            $position = $query->first();

            if ($position) {
                return $position->position;
            }

            return '';

        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (!function_exists('UngetStudentMeritPosition')) {
    function UngetStudentMeritPosition($un_academic_id, $un_semester_label_id, $exam_term_id, $record_id,$un_section_id,$un_faculty_id,$un_department_id)
    {
        try {
            $position = ExamMeritPosition::withOutGlobalScopes()->where('un_academic_id', $un_academic_id)
                ->where('un_semester_label_id', $un_semester_label_id)

                ->where('exam_term_id', $exam_term_id)
                ->where('record_id', $record_id)
                ->where('un_section_id',$un_section_id)
                ->where('un_faculty_id',$un_faculty_id)
                ->where('un_department_id',$un_department_id)
                ->first();
            if ($position) {
                return $position->position;
            } else {
                return '';
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}

if (!function_exists('getStudentAllExamMeritPosition')) {
    function getStudentAllExamMeritPosition($class_id, $section_id, $record_id)
    {
        try {
            $position = AllExamWisePosition::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('record_id', $record_id)
                ->first();
            if ($position) {
                return $position->position;
            }

            return null;

        } catch (Throwable $throwable) {
            return false;
        }
    }
}

if (! function_exists('gpaResult')) {
    function gpaResult($gpa)
    {
        $mark = SmMarksGrade::where('gpa', floor($gpa))->first();
        if ($mark) {
            return $mark;
        }

        return null;

    }
}

if (! function_exists('getGlobalExamBySecClsSub')) {
    function getGlobalExamBySecClsSub($section_id, $class_id, $subject_id)
    {
        $globalExams = SmExam::withoutGlobalScope(AcademicSchoolScope::class)
            ->withoutGlobalScope(GlobalAcademicScope::class)
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->where('section_id', $section_id)
            ->with('GetGlobalExamTitle')
            ->get();
        if ($globalExams) {
            return $globalExams;
        }

        return [];

    }
}

if (! function_exists('db_engine')) {
    function db_engine()
    {
        try {
            return \DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (Exception $exception) {
            return 'mysql';
        }
    }
}

if (! function_exists('examReportSignatures')) {
    function examReportSignatures()
    {
        return SmExamSignature::where('active_status', 1)->get(['title', 'signature']);
    }
}

if (! function_exists('send_notification')) {
    function send_notification($event, $user_id, $data)
    {
        $notification = SmNotificationSetting::where('event', $event)->where('school_id', auth()->user()->school_id)->first();
        $user = User::find($user_id);
        $all_recivers = $notification->recipient;
        $reciver = '';
        $active_recivers = [];
        $active_dest = [];
        $body = '';
        if ($user->role_id == 1) {
            $reciver = 'Admin';
        } elseif ($user->role_id == 2) {
            $reciver = 'Student';
        } elseif ($user->role_id == 3) {
            $reciver = 'Parent';
        } elseif ($user->role_id == 4) {
            $reciver = 'Teacher';
        }

        foreach ($all_recivers as $key => $value) {
            if ($value == 1) {
                $active_recivers[] = $key;
            }
        }

        if (in_array($reciver, $active_recivers)) {
            $destinations = $notification->destination;

            foreach ($destinations as $via => $value) {
                if ($value == 1) {
                    $active_dest[] = $via;
                }
            }

            if (in_array('Email', $active_dest)) {
                $body = short_code_messege($notification->template[$reciver]['Email'], $data);
                $view = view('backEnd.email.emailBody', ['body' => $body]);
                $message = (string) $view;
                $headers = "From: <$user->email> \r\n";
                $headers .= "Reply-To: $user->full_name <$user->email> \r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";
                @mail($user->email, $event, $message, $headers);
            }

            if (in_array('SMS', $active_dest)) {
                $sms_body = short_code_messege($notification->template[$reciver]['SMS'], $data);
            }

            if (in_array('Web', $active_dest)) {
                $web_body = short_code_messege($notification->template[$reciver]['Web'], $data);

                $notification = new SmNotification;
                $notification->user_id = $user->id;
                $notification->role_id = $user->role_id;
                $notification->school_id = $user->school_id;
                $notification->academic_id = getAcademicId();
                $notification->date = date('Y-m-d');
                $notification->message = $web_body;
                $notification->save();
            }

            if (in_array('App', $active_dest)) {
            }
        }

        return SmExamSignature::where('active_status', 1)->get(['title', 'signature']);
    }
}

if (! function_exists('short_code_messege')) {
    function short_code_messege($templete, array $data)
    {
        $templete = str_replace('[class]', @$data['class'], $templete);
        $templete = str_replace('[section]', @$data['section'], $templete);
        $templete = str_replace('[teacher_name]', @$data['teacher_name'], $templete);
        $templete = str_replace('[admin_name]', @$user->full_name, $templete);

        return str_replace('[student_name]', gv($data, 'student_name', @$user->full_name), $templete);
    }
}

if (! function_exists('feesCarryForward')) {
    function feesCarryForward($studentRecordId, $feesType, array $payableAmount, $sub_total): ?array
    {
        $carryForward = SmFeesCarryForward::where('student_id', $studentRecordId)->first();
        
        if (! $carryForward) {
            return null;
        }

        $settings = FeesCarryForwardSettings::first();
        
        if (Carbon::now()->format('Y-m-d') <= $carryForward->due_date) {
                
            $totalPayableAmount = 0;
            foreach ($payableAmount as $amount) {
                $totalPayableAmount += $amount;
            }
            
            if ($carryForward->balance_type == 'due' && $carryForward->balance > 0) {
               
                $dueBalance = $carryForward->balance;
                if ($carryForward) {
                    $fees_type = new FmFeesType();
                    $fees_type->type = 'fees_carry';
                    $fees_type->name = $carryForward->notes ?: $settings->title;
                    $fees_type->school_id = auth()->user()->school_id;
                    $fees_type->academic_id = getAcademicId();
                    $fees_type->save();
                }

                $data['feesTypes'] = array_merge($feesType, [(int) $fees_type->id]);
                $data['amount'] = array_merge($payableAmount, [(int) $dueBalance]);
                $data['sub_total'] = array_merge($sub_total, [(int) $dueBalance]);
                $data['type'] = 'due';
                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecordId)->first();
                $updateCarry->balance = 0;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();
                carryForwardLog($studentRecordId, $dueBalance, 'due', 'Fees Payment', 'fees');
            } elseif ($totalPayableAmount <= $carryForward->balance) {
                $addBalance = $carryForward->balance - $totalPayableAmount;
                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecordId)->first();
                $updateCarry->balance = $addBalance;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();
                carryForwardLog($studentRecordId, $totalPayableAmount, 'due', 'Fees Payment Added', 'fees');
                carryForwardLog($studentRecordId, $addBalance, 'add', 'Fees Payment and Carry Ballance Added', 'fees');
                $data['paymentAmount'] = $payableAmount;
                $data['type'] = 'full_paid_add_xtra_amount';
            } else {
                
                $cAmount = $carryForward->balance;
                
                $paidFeesType = [];
                $paidFeesAmount = [];
                
                foreach ($feesType as $key => $type) {
                   
                    $paidFeesType[$key] = $type;
                   
                    if ($cAmount > 0) {
                         
                        $pAmount = $payableAmount[$key] * 1;
                        
                        if ($cAmount >= $pAmount) {
                            $paidFeesAmount[$key] = $pAmount;
                            $cAmount -= $pAmount;
                        } elseif ($cAmount < $pAmount) {
                            $paidFeesAmount[$key] = $cAmount;
                            $cAmount = 0;
                        } else {
                            $paidFeesAmount[$key] = 0;
                        }
                    }
                }

                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecordId)->first();
                
                $updateCarry->balance = null;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();
                
                carryForwardLog($studentRecordId, $cAmount, 'due', 'Fees Payment', 'fees');

                $data['paidFeesType'] = $paidFeesType;
                $data['paidFeesAmount'] = $paidFeesAmount;
                $data['type'] = 'multi_payment';
            }

            $data['paymentMethod'] = $settings->payment_gateway;
            return $data;
        }

        return null;

    }
}

if (! function_exists('carryForwardLog')) {
    function carryForwardLog($record_id, $amount, $amount_type, $note, $type): bool
    {
        $storeLog = new FeesCarryForwardLog();
        $storeLog->student_record_id = $record_id;
        $storeLog->amount = $amount;
        $storeLog->amount_type = $amount_type;
        $storeLog->date = date('Y-m-d H:i:s');
        $storeLog->note = $note;
        $storeLog->type = $type;
        $storeLog->created_by = auth()->user()->id;
        $storeLog->school_id = auth()->user()->school_id;
        $storeLog->save();

        return true;
    }
}

if (! function_exists('averagePassingMark')) {
    function averagePassingMark($exam_type_id)
    {
        $examType = SmExamType::find($exam_type_id);
        if ($examType && $examType->is_average == 1) {
            return $examType->average_mark;
        }

        return null;
    }
}

if (! function_exists('inAppLiveClassJoinAndClose')) {
    function inAppLiveClassJoinAndClose($classMeeting): bool
    {
        $currentDayStatus = Carbon::now();
        $currentTime = $currentDayStatus->format('g:i A');
        $givenTime = Carbon::parse($classMeeting->time)->addMinutes((int) $classMeeting->duration)->format('g:i A');
        if ($currentDayStatus->format('Y-m-d') <= Carbon::parse($classMeeting->date)->format('Y-m-d') && is_null($classMeeting->end_at)) {
            if ($currentTime >= Carbon::parse($classMeeting->time)->format('g:i A')) {
                return $currentDayStatus->isBetween($currentTime, $givenTime);
            }

            return false;

        }

        return false;

    }
}

if (! function_exists('storeCalendarInfo')) {
    function storeCalendarInfo(
        $title,
        $description,
        $date,
        $created_by,
        $record_id = null,
        $role_id = null,
        $staff_id = null,
        $parent_id = null,
        $class_id = null,
        $section_id = null,
        $url = null

    ): void {
        $storeData = new SmCalendar();
        $storeData->record_id = $record_id;
        $storeData->role_id = $role_id;
        $storeData->staff_id = $staff_id;
        $storeData->parent_id = $parent_id;
        $storeData->class_id = $class_id;
        $storeData->section_id = $section_id;
        $storeData->title = $title;
        $storeData->description = $description;
        $storeData->url = $url;
        $storeData->date = $date;
        $storeData->created_by = $created_by;
        $storeData->school_id = auth()->user()->school_id;
        $storeData->academic_id = getAcademicId();
        $storeData->save();
    }
}

if (! function_exists('getYoutubeName')) {
    function getYoutubeName($link): string
    {
        return explode('</title>', explode('<title>', file_get_contents($link))[1])[0];
    }
}

if (! function_exists('youtubeVideoLinkValidation')) {
    function youtubeVideoLinkValidation($link)
    {
        return preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]{11})/", $link);
    }
}

if (! function_exists('generateRandomString')) {
    function generateRandomString($length): string
    {
        $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
        $validCharsLen = mb_strlen($validChars);
        $str = '';
        $i = 0;
        while ($i++ < $length) {
            $str .= $validChars[random_int(0, $validCharsLen - 1)];
        }

        return $str;
    }
}

if (! function_exists('getSubjectAttendance')) {
    function getSubjectAttendance($record)
    {
        return SmSubjectAttendance::with('student')
            ->whereIn('academic_id', $record->pluck('academic_id'))
            ->whereIn('student_record_id', $record->pluck('id'))
            ->whereIn('school_id', $record->pluck('school_id'))
            ->get();
    }
}

if (! function_exists('activeTheme')) {
    function activeTheme()
    {
        try {
            return generalSetting()->active_theme;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (! function_exists('getPermissions')) {
    function getPermissions()
    {
        Cache::rememberForever('permissions', function (): void {
            Permission::with(['subModule'])->get();
        });

        return Cache::get('permissions');
    }
}

if (! function_exists('userLocal')) {
    function userLocal()
    {
        try {
            $user = auth()->user();

            return $user->language ?? App::getLocale();
        } catch (Throwable $throwable) {
            return 'en';
        }
    }
}

if (! function_exists('_translation')) {
    function _translation($key)
    {
        $trans = trans($key);
        try {
            $exp = explode('.', $trans);
            if (count($exp) == 2) {
                $txt = Str::replace('_', ' ', ucfirst($exp[1]));

                return ucfirst($txt);
            }

            $txt = $trans;
            $txt = Str::replace('_', ' ', ucfirst($txt));

            return ucfirst($txt);
        } catch (Throwable $throwable) {
            return $key;
        }
    }
}

if (! function_exists('_trans')) {
    function _trans($value)
    {

        try {
            if (env('APP_ENV') == 'production') {
                return trans($value);
            }

            $local = userLocal() ?: app()->getLocale();

            $langPath = resource_path('lang/'.$local.'/');

            if (! file_exists($langPath)) {
                mkdir($langPath, 0777, true);
            }

            if (str_contains($value, '.')) {
                $new_trns = explode('.', $value);
                $file_name = $new_trns[0];
                // $trans_key = $new_trns[1];
                $trans_key = str_replace($file_name.'.', '', $value);

                $file_path = $langPath.''.$file_name.'.php';
                if (file_exists($file_path)) {
                    $file_content = include $file_path;

                    if (array_key_exists($trans_key, $file_content)) {
                        return _translation($value);
                    }

                    $file_content[$trans_key] = $trans_key;
                    $str = <<<'EOT'
                                            <?php
                                                return [
                                            EOT;
                    foreach ($file_content as $key => $val) {
                        if (gettype($val) == 'string') {

                            $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                        }

                        if (gettype($val) == 'array') {
                            $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                            $str .= $line;
                            foreach ($val as $lang_key => $lang_val) {

                                $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                                $str .= $line;
                            }

                            $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                        }

                        $str .= $line;
                    }

                    $end = <<<'EOT'
                                                    ]
                                            ?>
                                            EOT;
                    $str .= $end;

                    file_put_contents($file_path, $str, $flags = 0, $context = null);

                } else {

                    fopen($file_path, 'w');
                    $file_content = [];
                    $file_content[$trans_key] = $trans_key;
                    $str = <<<'EOT'
                                            <?php
                                                return [
                                            EOT;
                    foreach ($file_content as $key => $val) {
                        if (gettype($val) == 'string') {

                            $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                        }

                        if (gettype($val) == 'array') {
                            $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                            $str .= $line;
                            foreach ($val as $lang_key => $lang_val) {

                                $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                                $str .= $line;
                            }

                            $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                        }

                        $str .= $line;
                    }

                    $end = <<<'EOT'
                                                    ]
                                            ?>
                                            EOT;
                    $str .= $end;

                    file_put_contents($file_path, $str, $flags = 0, $context = null);
                }

                return _translation($value);
            }

            $trans_key = $value;
            $file_path = resource_path('lang/'.$local.'/'.$local.'.php');

            fopen($file_path, 'w');
            $file_content = [];
            $file_content[$trans_key] = $trans_key;
            $str = <<<'EOT'
                                            <?php
                                                return [
                                            EOT;
            foreach ($file_content as $key => $val) {
                if (gettype($val) == 'string') {

                    $line = <<<EOT
                                                                    "{$key}" => "{$val}",\n
                                                                EOT;
                }

                if (gettype($val) == 'array') {
                    $line = <<<EOT
                                                                            "{$key}" => [\n
                                                                        EOT;
                    $str .= $line;
                    foreach ($val as $lang_key => $lang_val) {

                        $line = <<<EOT
                                                                            "{$lang_key}" => "{$lang_val}",\n
                                                                        EOT;

                        $str .= $line;
                    }

                    $line = <<<EOT
                                                                        ],\n
                                                                    EOT;
                }

                $str .= $line;
            }

            $end = <<<'EOT'
                                                    ]
                                            ?>
                                            EOT;
            $str .= $end;

            file_put_contents($file_path, $str, $flags = 0, $context = null);

            return _translation($value);

        } catch (Exception $exception) {
            return $value;
        }
    }
}

if (! function_exists('defaultLogo')) {
    function defaultLogo($path)
    {
        if ($path && file_exists($path)) {
            return asset($path);
        }

        return asset('public/uploads/settings/logo.png');

    }
}

if (! function_exists('defaultUserLogo')) {
    function defaultUserLogo($path)
    {
        if ($path && file_exists($path)) {
            return asset($path);
        }

        return asset('public/uploads/staff/demo/staff.jpg');

    }
}

if (! function_exists('latterAvater')) {
    function latterAvater($string): string
    {
        $words = explode(' ', $string);
        if (count($words) > 1) {
            return mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1);
        }

        $first = mb_substr($string, 0, 1);
        $last = mb_substr($string, -1);

        return $first.$last;
    }
}

if (! function_exists('getProfileImage')) {
    function getProfileImage($user_id)
    {
        $user = User::find($user_id);
        $role_id = $user->role_id;
        $student = SmStudent::where('user_id', $user_id)->first();
        $parent = SmParent::where('user_id', $user_id)->first();
        $staff = SmStaff::where('user_id', $user_id)->first();
        if ($role_id == 2) {
            $profile = $student->student_photo ?: 'public/backEnd/assets/img/avatar.png';
        } elseif ($role_id == 3) {
            $profile = $parent->fathers_photo ?: 'c';
        } else {
            $profile = $staff->staff_photo ?: 'public/backEnd/assets/img/avatar.png';
        }

        return $profile;
    }

    function headerContent()
    {
        $headerPageData = Page::where('school_id', app('school')->id)->where('name', 'header_menu')
            ->select('id', 'name', 'title', 'description', 'slug', 'settings', 'status')
            ->first();
        if ($headerPageData) {
            return view('pagebuilder::components.header-footer-page-components', ['page' => $headerPageData]);
        }

        return null;
    }

    function footerContent()
    {
        $footerPage = Page::where('school_id', app('school')->id)->where('name', 'footer_menu')
            ->select('id', 'name', 'title', 'description', 'slug', 'settings', 'status')
            ->first();
        if ($footerPage) {
            return view('pagebuilder::components.header-footer-page-components', ['page' => $footerPage]);
        }

        return null;
    }

    function formatedDate($date): string
    {
        return date('Y-m-d', strtotime($date));
    }
}

if (! function_exists('envu')) {
    function envu($data = []): bool
    {
        foreach ($data as $key => $value) {
            if (env($key) == $value) {
                unset($data[$key]);
            }
        }

        if (count($data) == 0) {
            return false;
        }

        $env = file_get_contents(base_path().'/.env');
        $env = explode("\n", $env);
        foreach ((array) $data as $key => $value) {
            foreach ($env as $env_key => $env_value) {
                $entry = explode('=', $env_value, 2);
                if ($entry[0] == $key) {
                    $env[$env_key] = $key.'='.(is_string($value) ? '"'.$value.'"' : $value);
                } else {
                    $env[$env_key] = $env_value;
                }
            }
        }

        $env = implode("\n", $env);
        file_put_contents(base_path().'/.env', $env);

        return true;
    }
}

if (! function_exists('lastOneMonthDates')) {
    /**
     * @return string[]
     */
    function lastOneMonthDates(): array
    {
        $days_ago = [];
        for ($i = 30; $i >= 1; $i--) {
            $day = date('Y-m-d', strtotime('-'.$i.' days', strtotime(date('Y-m-d'))));
            $days_ago[] = $day;
        }

        return $days_ago;
    }
}

if (! function_exists('insertMenuManage')) {
    function insertMenuManage($menu): void
    {
        $menuData = SmHeaderMenuManager::create($menu);
        if (gv($menu, 'childs')) {
            foreach (gv($menu, 'childs') as $child) {
                $child['parent_id'] = $menuData->id;
                insertMenuManage($child);
            }
        }
    }
}

// if (!function_exists('asset_path')) {
//     function asset_path($path = null)
//     {
//         return 'public/' . $path;
//     }
// }

if (! function_exists('asset_path')) {
    function asset_path($path = null)
    {
        return public_path($path);
    }
}

if (! function_exists('forumSetting')) {
    function forumSetting()
    {
        return ForumSetting::where('school_id', 1)->withoutGlobalScopes()->first();
    }
}

if (! function_exists('get_logo')) {
    function get_logo()
    {
        $generalSetting = generalSetting();
        $logoPath = $generalSetting->logo;

        if (! empty($logoPath) && file_exists(public_path($logoPath))) {
            return asset($logoPath);
        }

        return asset('public/uploads/settings/logo.png');

    }
}

if (! function_exists('generateQRCode')) {
    function generateQRCode(string $text)
    {

        try {
            if (! file_exists(public_path('qr_codes/'.$text.'-qrcode.png'))) {
                $qr_renderer = new ImageRenderer(
                    new RendererStyle(400, 1),
                    new ImagickImageBackEnd()
                );
                $writer = new Writer($qr_renderer);
                $writer->writeFile($text, public_path('qr_codes/'.$text.'-qrcode.png'));

                return asset('public/qr_codes/'.$text.'-qrcode.png');
            }
        } catch (Exception $exception) {
            Log::error('Error on QR code Generate '.$exception->getMessage());
        }

        return null;
    }
}

if (! function_exists('qrAttendanceSetting')) {
    function qrAttendanceSetting($class, $section, $subject = null)
    {
        $setting = new QRCodeAttendanceSetting();
        $setting = $setting->where('class_id', $class)
            ->where('section_id', $section)
            ->where('school_id', auth()->user()->school_id);
        if ($subject) {
            $setting = $setting->where('subject_id', $subject);
        }

        return $setting->first();

    }
}

if (! function_exists('generateDatePeriod')) {
    /**
     * @return string[]
     */
    function generateDatePeriod($first, $last, $step = '+1 day', $output_format = 'Y-m-d'): array
    {

        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }
}

if (! function_exists('dayNames')) {
    function dayNames(): array
    {

        return [
            'Saturday' => 'Friday',
            'Sunday' => 'Saturday',
            'Monday' => 'Sunday',
            'Tuesday' => 'Monday',
            'Wednesday' => 'Tuesday',
            'Thursday' => 'Wednesday',
            'Friday' => 'Thursday',
        ];
    }
}

if (! function_exists('getWeekendDay')) {
    function getWeekendDay($day)
    {
        return dayNames()[$day];
    }
}

if (! function_exists('getWeekNumber')) {
    function getWeekNumber($date): string
    {
        // Convert the date to a Unix timestamp
        $timestamp = strtotime($date);

        // Get the ISO-8601 week number
        $weekNumber = date('W', $timestamp);

        return $weekNumber;
    }
}

if (! function_exists('toastrError')) {
    function toastrError($message = 'Operation Failed', $title = 'Failed'): void
    {
        $toastr = app(Brian2694\Toastr\Toastr::class);
        $toastr->error($message, $title);
    }
}

if (! function_exists('toastrSuccess')) {
    function toastrSuccess($message = 'Operation Success', $title = 'Success'): void
    {
        $toastr = app(Brian2694\Toastr\Toastr::class);
        $toastr->success($message, $title);
    }
}

if (! function_exists('toastrWarning')) {
    function toastrWarning($message = 'Operation Warning', $title = 'Warning'): void
    {
        $toastr = app(Brian2694\Toastr\Toastr::class);
        $toastr->warning($message, $title);
    }
}

if (! function_exists('ad')) {
    function ad(mixed ...$vars): void
    {
        if (config('app.debug')) {
            foreach ($vars as $key => $value) {
                Log::info(is_int($key) ? 'Variable '.$key : $key, [
                    'dump' => print_r($value, true),
                ]);
            }

            dd(...$vars);
        }
    }
}

// if (! function_exists('showTimelineDocName')) {
// function showTimelineDocName($data)
// {
//     $name = explode('/', $data);
//     $number = count($name);
//     return $name[$number - 1];
// }
// }

// if (! function_exists('showDocumentName')) {
//     function showDocumentName($data)
//     {
//         $name = explode('/', $data);
//         $number = count($name);
//         return $name[$number - 1];
//     }
// }

if (! function_exists('shiftEnable')) {
    function shiftEnable(): bool
    {
        try {
            $generalSetting = generalSetting();
            return isset($generalSetting->shift_enable) && (bool) $generalSetting->shift_enable;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (! function_exists('validationMessage')) {
    function validationMessage(array $rules): array
    {
        return [
            'file.required' => 'Please upload a file.',
            'file.mimes' => 'Only CSV, XLS, or XLSX files are allowed.',
            'file.max' => 'File size must not exceed 2MB.',
            'index.required' => 'Index mapping is required.',
            'index.array' => 'Index must be an array.',
        ];
    }
}

if (!function_exists('convertToSnakeCase')) {
    function convertToSnakeCase($string)
    {
        return \Illuminate\Support\Str::snake($string);
    }
}

if (!function_exists('convertToTitleCase')) {
    function convertToTitleCase($string)
    {
        return \Illuminate\Support\Str::title(str_replace('_', ' ', $string));
    }
}