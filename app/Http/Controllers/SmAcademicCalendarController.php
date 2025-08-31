<?php

namespace App\Http\Controllers;

use App\Role;
use Exception;
use App\SmEvent;
use App\SmHoliday;
use Carbon\Carbon;
use App\SmHomework;
use App\SmBookIssue;
use App\SmOnlineExam;
use App\SmNoticeBoard;
use App\GlobalVariable;
use App\SmExamSchedule;
use App\SmLeaveRequest;
use App\SmAdmissionQuery;
use Illuminate\Http\Request;
use App\SmTeacherUploadContent;
use App\Models\SmCalendarSetting;
use Brian2694\Toastr\Facades\Toastr;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\RolePermission\Entities\InfixRole;

class SmAcademicCalendarController extends Controller
{
    public function academicCalendarView()
    {
        $data['settings'] = SmCalendarSetting::get();
        $data['roles'] = InfixRole::where('is_saas', 0)->where(function ($q): void {
            $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
        })
            ->whereNotIn('id', [1])
            ->get();

        $data['events'] = $this->calenderData();

        return view('backEnd.communicate.academicCalendar', $data);
    }

    public function storeAcademicCalendarSettings(Request $request)
    {
            $calender_setting = SmCalendarSetting::all();
            foreach (gv($request, 'setting') as $key => $data) {
                $settings = $calender_setting->where('menu_name', $key)->first();
                $settings->status = gv($data, 'status');
                $settings->font_color = gv($data, 'font_color');
                $settings->bg_color = gv($data, 'bg_color');
                $settings->school_id = auth()->user()->school_id;
                $settings->update();
            }

            Toastr::success('Update Successfully', 'Success');

            return redirect()->route('academic-calendar');
    }

    public function allRoles()
    {
        return Role::select(['id', 'name'])->get();
    }

    public function getRoleNames($role_ids)
    {
        return Role::whereIn('id', explode(',', $role_ids))->pluck('name');
    }

    public function getCalendarSettings($menu_name)
    {
        return calandarSettingByMenuName($menu_name);
    }

    public function alumniGetCalendarSettings($menu_name)
    {
        if (auth()->user()->role_id == GlobalVariable::isAlumni()) {
            $data['status'] = 0;

            return (object) $data;
        }

        return calandarSettingByMenuName($menu_name);

    }

    public function calenderData()
    {
        // CalenderQuery Start

            $admissionQuries = [];
            $homeworks = [];
            $studyMaterials = [];
            $allEvents = [];
            $holidays = [];
            $examRounines = [];
            $noticeBoards = [];
            $onlineExams = [];
            $lessonPlans = [];
            $leaveDatas = [];
            $libraryDatas = [];

            $admissionQuerySettings = $this->alumniGetCalendarSettings('admission_query');
            $homeworkSettings = $this->alumniGetCalendarSettings('homework');
            $studyMaterialSettings = $this->alumniGetCalendarSettings('study_material');
            $eventSettings = $this->getCalendarSettings('event');
            $holidaySettings = $this->alumniGetCalendarSettings('holiday');
            $examSettings = $this->alumniGetCalendarSettings('exam');
            $noticeBoardSettings = $this->getCalendarSettings('notice_board');
            $onlineExamSettings = $this->alumniGetCalendarSettings('online_exam');
            $lessonPlanSettings = $this->alumniGetCalendarSettings('lesson_plan');
            $leaveSettings = $this->alumniGetCalendarSettings('leave');
            $librarySettings = $this->alumniGetCalendarSettings('library');

            $login_user_data = auth()->user();
            $userRoleId = $login_user_data->role_id;
            $roleInfo = $login_user_data->roles;

            $studentRecords = $userRoleId == 2 ? $login_user_data->student->studentRecords : [];
            $childrenRecords = [];
            if ($userRoleId == 3) {
                $childrenRecords = [];
                $childrenUserids = [];
                $childrenInfos = auth()->user()->parent->childrens;
                //dd($childrenInfos);
                foreach ($childrenInfos as $childInfo) {
                    $childrenRecords['class_id'] = $childInfo->studentRecords->pluck('class_id')->toArray();
                    $childrenRecords['section_id'] = $childInfo->studentRecords->pluck('section_id')->toArray();
                    $childrenUserids[] = $childInfo->user_id;
                }
                
                
            } else {
                $childrenRecords = [];
                $childrenUserids = [];
            }
            
            //dd($childrenRecords);

            $teacherAccess = $userRoleId == 4 ? auth()->user()->staff->classes->pluck('id') : [];

            if ($admissionQuerySettings->status == 1 && ($userRoleId != 2 && $userRoleId != 3)) {
                $admissionQuries = SmAdmissionQuery::get(['name', 'phone', 'email', 'address', 'next_follow_up_date']);
            }

            if ($homeworkSettings->status == 1) {
                $homeworks = SmHomework::with('class', 'section', 'subjects')
                    ->when($userRoleId == 2, function ($s) use ($studentRecords): void {
                        $class_ids = $studentRecords->pluck('class_id')->toArray();
                        $section_ids = $studentRecords->pluck('section_id')->toArray();
                        $s->whereIn('class_id', $class_ids);
                        $s->orWhereIn('section_id', $section_ids);
                    })
                    ->when($userRoleId == 3, function ($s) use ($childrenRecords): void {
                          
                        $s->whereIn('class_id', gv($childrenRecords, 'class_id',[]));
                        $s->orWhereIn('section_id', gv($childrenRecords, 'section_id',[]));
                    })
                    ->when($userRoleId == 4, function ($t) use ($teacherAccess): void {
                        $t->whereIn('class_id', $teacherAccess);
                        $t->orWhere('created_by', auth()->user()->staff->id);
                    })
                    ->select(['class_id', 'section_id', 'subject_id', 'description', 'submission_date'])
                    ->get();
            }

            if ($studyMaterialSettings->status == 1) {
                $studyMaterials = SmTeacherUploadContent::with('classes', 'sections')
                    ->when($userRoleId == 2, function ($s) use ($studentRecords): void {
                        $class_ids = $studentRecords->pluck('class_id')->toArray();
                        $section_ids = $studentRecords->pluck('section_id')->toArray();
                        $s->whereIn('class', $class_ids);
                        $s->orWhereIn('section', $section_ids);
                        $s->orWhere('available_for_all_classes', 1);
                    })
                    ->when($userRoleId == 3, function ($s) use ($childrenRecords): void {
                      
                        $s->whereIn('class', gv($childrenRecords, 'class_id',[]));
                        $s->orWhereIn('section', gv($childrenRecords, 'section_id',[]));
                    })
                    ->when($userRoleId != 2 && $userRoleId != 3, function ($a): void {
                        $a->where('available_for_admin', 1);
                    })
                    ->when($userRoleId == 4, function ($t) use ($teacherAccess): void {
                        $t->whereIn('class', $teacherAccess);
                        $t->orWhere('created_by', auth()->user()->staff->id);
                    })
                    ->get(['content_title', 'content_type', 'description', 'upload_date', 'upload_file', 'class', 'section']);
            }

            if ($eventSettings->status == 1) {
                $allEvents = SmEvent::when($roleInfo->name != 'Super admin', function ($a) use ($roleInfo): void {
                    $a->whereJsonContains('role_ids', (string) $roleInfo->id);
                })
                    ->get(['event_title', 'event_location', 'event_des', 'from_date', 'to_date', 'uplad_image_file', 'url']);
            }

            if ($holidaySettings->status == 1) {
                $holidays = SmHoliday::where('active_status', 1)->get(['holiday_title', 'details', 'from_date', 'to_date', 'upload_image_file']);
            }

            if ($examSettings->status == 1) {
                $examRounines = SmExamSchedule::with('class', 'section', 'subject', 'examType', 'teacher', 'classRoom')
                    ->when($userRoleId == 2, function ($s) use ($studentRecords): void {
                        $class_ids = $studentRecords->pluck('class_id')->toArray();
                        $section_ids = $studentRecords->pluck('section_id')->toArray();
                        $s->whereIn('class_id', $class_ids);
                        $s->orWhereIn('section_id', $section_ids);
                    })
                    ->when($userRoleId == 3, function ($s) use ($childrenRecords): void {
                        $s->whereIn('class_id', gv($childrenRecords, 'class_id',[]));
                        $s->orWhereIn('section_id', gv($childrenRecords, 'section_id',[]));
                    })
                    ->when($userRoleId == 4, function ($t) use ($teacherAccess): void {
                        $t->whereIn('class_id', $teacherAccess);
                        $t->orWhere('created_by', auth()->user()->staff->id);
                    })
                    ->where('active_status', 1)
                    ->get(['exam_term_id', 'subject_id', 'class_id', 'section_id', 'start_time', 'end_time', 'teacher_id', 'room_id', 'date']);
            }

            if ($noticeBoardSettings->status == 1) {
                $noticeBoards = SmNoticeBoard::where('publish_on', '<=', date('Y-m-d'))->when($roleInfo->name != 'Super admin', function ($a) use ($roleInfo): void {
                    $a->whereJsonContains('inform_to', (string) $roleInfo->id);
                })
                    ->get(['notice_title', 'notice_message', 'publish_on', 'inform_to']);
            }

            if ($onlineExamSettings->status == 1) {
                $onlineExams = SmOnlineExam::with('class', 'section', 'subject')
                    ->when($userRoleId == 2, function ($s) use ($studentRecords): void {
                        $class_ids = $studentRecords->pluck('class_id')->toArray();
                        $section_ids = $studentRecords->pluck('section_id')->toArray();
                        $s->whereIn('class_id', $class_ids);
                        $s->orWhereIn('section_id', $section_ids);
                    })
                    ->when($userRoleId == 3, function ($s) use ($childrenRecords): void {
                        $s->whereIn('class_id', gv($childrenRecords, 'class_id',[]));
                        $s->orWhereIn('section_id', gv($childrenRecords, 'section_id',[]));
                    })
                    ->when($userRoleId == 4, function ($t) use ($teacherAccess): void {
                        $t->whereIn('class_id', $teacherAccess);
                        $t->orWhere('created_by', auth()->user()->staff->id);
                    })
                    ->get(['title', 'date', 'end_date_time', 'start_time', 'end_time', 'subject_id', 'class_id', 'section_id']);
            }

            if ($lessonPlanSettings->status == 1) {
                $lessonPlans = LessonPlanner::with(['class' => function ($c) {
                    return $c->select(['class_name', 'id']);
                }, 'sectionName', 'subject', 'teacherName'])
                    ->when($userRoleId == 2, function ($s) use ($studentRecords): void {
                        $class_ids = $studentRecords->pluck('class_id')->toArray();
                        $section_ids = $studentRecords->pluck('section_id')->toArray();
                        $s->whereIn('class_id', $class_ids);
                        $s->orWhereIn('section_id', $section_ids);
                    })
                    ->when($userRoleId == 3, function ($s) use ($childrenRecords): void {
                        $s->whereIn('class_id', gv($childrenRecords, 'class_id',[]));
                        $s->orWhereIn('section_id', gv($childrenRecords, 'section_id',[]));
                    })
                    ->when($userRoleId == 4, function ($t) use ($teacherAccess): void {
                        $t->whereIn('class_id', $teacherAccess);
                        $t->orWhere('created_by', auth()->user()->staff->id);
                    })
                    ->get(['lesson_date', 'teacher_id', 'subject_id', 'class_id', 'section_id']);
            }

            if ($leaveSettings->status == 1) {
                $leaveDatas = SmLeaveRequest::with('user')
                    ->when($roleInfo->name != 'Super admin' && $roleInfo->name != 'Parents', function ($a): void {
                        $a->where('staff_id', auth()->user()->id);
                    })
                    ->when($roleInfo->name == 'Parents', function ($p) use ($childrenUserids): void {
                        $p->whereIn('staff_id', $childrenUserids);
                    })
                    ->where('approve_status', 'A')->get(['leave_from', 'leave_to', 'reason', 'staff_id']);
            }

            if ($librarySettings->status == 1) {
                $libraryDatas = SmBookIssue::with('user')
                    ->when($roleInfo->name != 'Super admin', function ($a): void {
                        $a->where('member_id', auth()->user()->id);
                    })
                    ->where('issue_status', 'I')->get(['due_date', 'member_id', 'book_id']);
            }

            // CalenderQuery End

            $eventData = [];

            foreach ($admissionQuries as $admissionQury) {
                $eventData[] = [
                    'title' => __('communicate.admission_query').'- '.$admissionQury->name,
                    'name' => $admissionQury->name,
                    'phone' => $admissionQury->phone,
                    'email' => $admissionQury->email,
                    'address' => $admissionQury->address,
                    'start' => Carbon::parse($admissionQury->next_follow_up_date)->format('Y-m-d'),
                    'end' => Carbon::parse($admissionQury->next_follow_up_date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($admissionQury->next_follow_up_date)->format('Y-m-d'),
                    'route' => route('admission_query'),
                    'textColor' => $admissionQuerySettings->font_color,
                    'color' => $admissionQuerySettings->bg_color,
                    'type' => 'admission_query',
                ];
            }

            foreach ($homeworks as $homework) {
                $eventData[] = [
                    'title' => __('communicate.homework').'- '.$homework->description,
                    'class' => $homework->class->class_name ?? '',
                    'section' => $homework->section->section_name ?? '',
                    'subject' => $homework->subjects->subject_name ?? '',
                    'description' => $homework->description,
                    'start' => Carbon::parse($homework->submission_date)->format('Y-m-d'),
                    'end' => Carbon::parse($homework->submission_date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($homework->submission_date)->format('Y-m-d'),
                    'route' => route('homework-list'),
                    'textColor' => $homeworkSettings->font_color,
                    'color' => $homeworkSettings->bg_color,
                    'type' => 'homework',
                ];
            }

            foreach ($studyMaterials as $studyMaterial) {
                if ($studyMaterial->content_type == 'as') {
                    $type = __('study.assignment');
                } elseif ($studyMaterial->content_type == 'st') {
                    $type = __('study.study_material');
                } elseif ($studyMaterial->content_type == 'sy') {
                    $type = __('study.syllabus');
                } else {
                    $type = __('study.other');
                }

                if ($studyMaterial->available_for_admin == 1) {
                    $avaiable = app('translator')->get('study.all_admins');
                } elseif ($studyMaterial->available_for_all_classes == 1) {
                    $avaiable = app('translator')->get('study.all_classes_student');
                } elseif (!empty($studyMaterial->classes) && !empty($studyMaterial->sections)) {
                    $avaiable = app('translator')->get('study.all_students_of').' '.$studyMaterial?->classes?->class_name.'->'.@$studyMaterial?->sections?->section_name;
                } elseif (!empty($studyMaterial->classes) && !empty($studyMaterial->section )) {
                    $avaiable = app('translator')->get('study.all_students_of').' '.$studyMaterial?->classes?->class_name.'->'.app('translator')->get('study.all_sections');
                } else {
                    $avaiable = app('translator')->get('study.all_students_of');
                }

                $eventData[] = [
                    'title' => __('communicate.study_material').'- '.$studyMaterial->content_title,
                    'content_title' => $studyMaterial->content_title,
                    'content_type' => $type,
                    'description' => $studyMaterial->description,
                    'avaiable' => $avaiable,
                    'start' => Carbon::parse($studyMaterial->upload_date)->format('Y-m-d'),
                    'end' => Carbon::parse($studyMaterial->upload_date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($studyMaterial->upload_date)->format('Y-m-d'),
                    'textColor' => $studyMaterialSettings->font_color,
                    'color' => $studyMaterialSettings->bg_color,
                    'type' => 'study_material',
                ];
            }

            foreach ($allEvents as $allEvent) {
                $eventData[] = [
                    'title' => __('communicate.event').'- '.$allEvent->event_title,
                    'content_title' => $allEvent->event_title,
                    'description' => $allEvent->event_des,
                    'location' => $allEvent->event_location,
                    'image' => $allEvent->uplad_image_file,
                    'start' => Carbon::parse($allEvent->from_date)->format('Y-m-d'),
                    'end' => Carbon::parse($allEvent->to_date)->addDay()->format('Y-m-d'),
                    'endDate' => Carbon::parse($allEvent->to_date)->format('Y-m-d'),
                    'link' => $allEvent->url,
                    'textColor' => $eventSettings->font_color,
                    'color' => $eventSettings->bg_color,
                    'type' => 'event',
                ];
            }

            foreach ($holidays as $holiday) {
                $eventData[] = [
                    'title' => __('communicate.holiday').'- '.$holiday->holiday_title,
                    'title_content' => $holiday->holiday_title,
                    'description' => $holiday->details,
                    'start' => Carbon::parse($holiday->from_date)->format('Y-m-d'),
                    'end' => Carbon::parse($holiday->to_date)->addDay()->format('Y-m-d'),
                    'endDate' => Carbon::parse($holiday->to_date)->format('Y-m-d'),
                    'image' => $holiday->upload_image_file,
                    'textColor' => $holidaySettings->font_color,
                    'color' => $holidaySettings->bg_color,
                    'type' => 'holiday',
                ];
            }

            foreach ($examRounines as $examRounine) {
                $eventData[] = [
                    'title' => __('exam.exam_schedule').'- '.@$examRounine->examType->title ?? '',
                    'class' => $examRounine->class->class_name ?? '',
                    'section' => $examRounine->section->section_name ?? '',
                    'subject' => $examRounine->subject->subject_name ?? '',
                    'exam_term' => $examRounine->examType->title ?? '',
                    'start_time' => Carbon::parse($examRounine->start_time)->format('g:i A'),
                    'end_time' => Carbon::parse($examRounine->end_time)->format('g:i A'),
                    'teacher' => $examRounine->teacher->full_name ?? '',
                    'room' => $examRounine->classRoom->room_no ?? '',
                    'start' => Carbon::parse($examRounine->date)->format('Y-m-d'),
                    'end' => Carbon::parse($examRounine->date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($examRounine->date)->format('Y-m-d'),
                    'textColor' => $examSettings->font_color,
                    'color' => $examSettings->bg_color,
                    'type' => 'exam',
                ];
            }

            $roles = $this->allRoles();
            foreach ($noticeBoards as $noticeBoard) {
                $eventData[] = [
                    'title' => __('communicate.notice_board').'- '.$noticeBoard->notice_title,
                    'title_content' => $noticeBoard->notice_title,
                    'notice_message' => $noticeBoard->notice_message,
                    'inform_to' => $roles->where('id', $noticeBoard->inform_to)->pluck('name')->implode(',', ''), // $this->getRoleNames($notice->inform_to)->implode(', ') ?? '',
                    'start' => Carbon::parse($noticeBoard->publish_on)->format('Y-m-d'),
                    'end' => Carbon::parse($noticeBoard->publish_on)->format('Y-m-d'),
                    'endDate' => Carbon::parse($noticeBoard->publish_on)->format('Y-m-d'),
                    'textColor' => $noticeBoardSettings->font_color,
                    'color' => $noticeBoardSettings->bg_color,
                    'type' => 'notice_board',
                ];
            }

            foreach ($onlineExams as $onlineExam) {
                $eventData[] = [
                    'title' => __('communicate.online_exam').'- '.$onlineExam->title,
                    'title_content' => $onlineExam->title,
                    'class' => $onlineExam->class->class_name ?? '',
                    'section' => $onlineExam->section->section_name ?? '',
                    'subject' => $onlineExam->subject->subject_name ?? '',
                    'start_time' => Carbon::parse($onlineExam->start_time)->format('g:i A'),
                    'end_time' => Carbon::parse($onlineExam->end_time)->format('g:i A'),
                    'start' => Carbon::parse($onlineExam->date)->format('Y-m-d'),
                    'end' => Carbon::parse($onlineExam->end_date_time)->addDay()->format('Y-m-d'),
                    'endDate' => Carbon::parse($onlineExam->end_date_time)->format('Y-m-d'),
                    'textColor' => $onlineExamSettings->font_color,
                    'color' => $onlineExamSettings->bg_color,
                    'type' => 'online_exam',
                ];
            }

            foreach ($lessonPlans as $lessonPlan) {
                $eventData[] = [
                    'title' => __('communicate.lesson_plan'),
                    'class' => $lessonPlan->class->class_name ?? '',
                    'section' => $lessonPlan->sectionName->section_name ?? '',
                    'subject' => $lessonPlan->subject->subject_name ?? '',
                    'teacher' => $lessonPlan->teacherName->full_name ?? '',
                    'start' => Carbon::parse($lessonPlan->lesson_date)->format('Y-m-d'),
                    'end' => Carbon::parse($lessonPlan->lesson_date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($lessonPlan->lesson_date)->format('Y-m-d'),
                    'textColor' => $lessonPlanSettings->font_color,
                    'color' => $lessonPlanSettings->bg_color,
                    'type' => 'lesson_plan',
                ];
            }

            foreach ($leaveDatas as $leaveData) {
                $eventData[] = [
                    'title' => __('communicate.leave').'- '.$leaveData->user?->full_name ?? 'User',
                    'name' => $leaveData->user?->full_name ?? 'User',
                    'reason' => $leaveData->reason,
                    'start' => Carbon::parse($leaveData->leave_from)->format('Y-m-d'),
                    'end' => Carbon::parse($leaveData->leave_to)->addDay()->format('Y-m-d'),
                    'endDate' => Carbon::parse($leaveData->leave_to)->format('Y-m-d'),
                    'textColor' => $leaveSettings->font_color,
                    'color' => $leaveSettings->bg_color,
                    'type' => 'leave',
                ];
            }

            foreach ($libraryDatas as $libraryData) {
                $eventData[] = [
                    'title' => __('communicate.library').'- '.$libraryData->user?->full_name ?? 'User',
                    'book_name' => $libraryData->books->book_title ?? 'User',
                    'start' => Carbon::parse($libraryData->due_date)->format('Y-m-d'),
                    'end' => Carbon::parse($libraryData->due_date)->format('Y-m-d'),
                    'endDate' => Carbon::parse($libraryData->due_date)->format('Y-m-d'),
                    'textColor' => $librarySettings->font_color,
                    'color' => $librarySettings->bg_color,
                    'type' => 'library',
                ];
            }

            return $eventData;

    }
}
