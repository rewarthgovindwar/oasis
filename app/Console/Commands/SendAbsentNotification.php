<?php

namespace App\Console\Commands;

use App\SmStudent;
use App\SmsTemplate;
use Illuminate\Support\Str;
use App\SmSubjectAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SmCommunicateController;
use Modules\StudentAbsentNotification\Entities\AbsentNotificationTimeSetup;

class SendAbsentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absent_notification:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sms for student absent';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        date_default_timezone_set(timeZone());

        $current_time = date('H:i');
        $setup = AbsentNotificationTimeSetup::where('active_status', 1)
            ->where('time_from', $current_time)
            ->where('active_status', 1)
            ->first();
        if ($setup) {
            $is_school_day = SmSubjectAttendance::where('attendance_type', 'P')->where('attendance_date', date('Y-m-d'))->first();
            $now = strtotime(date('H:i'));
            $setup_time = (strtotime($setup->time_from));

            if ($is_school_day) {

                $absent_student = SmSubjectAttendance::where('attendance_type', 'A')
                    ->where('attendance_date', date('Y-m-d'))
                    ->leftjoin('sm_students', 'sm_students.id', '=', 'sm_subject_attendances.student_id')
                    ->leftjoin('sm_parents', 'sm_parents.id', '=', 'sm_students.parent_id')
                    ->select('sm_subject_attendances.*')
                    ->get();

                $students = [];
                $parent_email = [];
                $parent_mobile = [];
                foreach ($absent_student as $value) {
                    $students[] = $value->student_id;
                    $parent_email[] = $value->guardians_email;
                    $parent_mobile[] = $value->guardians_mobile;
                }

                $absent_subject_list = SmSubjectAttendance::getAbsentSubjectList(1);

                $sms_template = SmsTemplate::where('school_id', Auth::user()->school_id)->first();
                $template = $sms_template->student_absent_notification_sms;

                // Hi [fathers_name], Your child [student_name] absent for [number_of_subject] subjects. Those are [subject_list] on [date]. Thanks

                foreach (array_unique($students) as $student_id) {

                    $absent_subjects = SmSubjectAttendance::getAbsentSubjectList($student_id); // Array
                    $student_info = SmStudent::find($student_id);
                    $guardian_name = $student_info->parents->guardians_name;
                    $guardian_mobile = $student_info->parents->guardians_mobile;

                    $sms_template = SmsTemplate::where('school_id', Auth::user()->school_id)->first();
                    $template = $sms_template->student_absent_notification_sms;

                    $chars = preg_split('/[\s,]+/', $template, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                    $sms_text = '';
                    foreach ($chars as $char) {
                        if (Str::contains($char, '[')) {
                            $char = preg_replace('/[^A-Za-z0-9\-]/', '', $char);

                            if ($char == 'fathersname') {

                                $sms_text .= $guardian_name.', ';

                            } elseif ($char == 'studentname') {

                                $sms_text .= $student_info->full_name.' ';

                            } elseif ($char == 'numberofsubject') {

                                $sms_text .= count($absent_subjects).' ';

                            } elseif ($char == 'subjectlist') {

                                $sms_text .= implode(',', $absent_subjects).' ';

                            } elseif ($char == 'date') {

                                $sms_text .= date('Y-m-d').' ';

                            }
                        } else {
                            $sms_text .= $char.' ';
                        }
                    }

                    sendSMSBio($guardian_mobile, $sms_text);
                    // $this->SmCommunicateController->sendSMSFromComunicate($guardian_mobile, $sms_text);

                    // $setup=new AbsentNotificationTimeSetup();
                    // $setup->time_from= $guardian_mobile;
                    // $setup->time_to=$sms_text;
                    // $setup->active_status=1;
                    // $setup->save();

                }

            }
        }

    }
}
