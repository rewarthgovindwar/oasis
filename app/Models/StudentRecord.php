<?php

namespace App\Models;

use App\SmExam;
use App\SmClass;
use Carbon\Carbon;
use App\SmHomework;
use App\SmFeesAssign;
use App\SmOnlineExam;
use App\SmExamSchedule;
use App\SmAssignSubject;
use App\Scopes\SchoolScope;
use App\SmStudentAttendance;
use App\SmFeesAssignDiscount;
use App\SmTeacherUploadContent;
use App\SmStudentTakeOnlineExam;
use Modules\Lms\Entities\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Modules\Zoom\Entities\VirtualClass;
use Modules\ExamPlan\Entities\AdmitCard;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\BBB\Entities\BbbVirtualClass;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\University\Entities\UnSubject;
use Modules\Gmeet\Entities\GmeetVirtualClass;
use Modules\Jitsi\Entities\JitsiVirtualClass;
use Modules\OnlineExam\Entities\InfixOnlineExam;
use Modules\University\Entities\UnAssignSubject;
use Modules\OnlineExam\Entities\InfixWrittenExam;
use Modules\University\Entities\UnSubjectComplete;
use Modules\InAppLiveClass\Entities\InAppLiveClass;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use JoisarJignesh\Bigbluebutton\Facades\Bigbluebutton;
use Modules\University\Entities\UnSubjectPreRequisite;
use Modules\University\Entities\UnSubjectAssignStudent;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;

class StudentRecord extends Model
{
    use HasFactory;

    protected $casts = [
        'class_id' => 'integer',
        'section_id' => 'integer',
        'student_id' => 'integer',
        'academic_id' => 'integer',
        'is_default' => 'integer',
        'is_promote' => 'integer',
        'session_id' => 'integer',
        'school_id' => 'integer',
        'roll_no' => 'integer',
    ];

    protected $guarded = [
        'id',
    ];

    public static function getInfixStudentTakeOnlineExamParent($student_id, $record_id)
    {
        if (moduleStatusCheck('OnlineExam') == true) {
            return InfixStudentTakeOnlineExam::where('status', 2)
                ->where('student_id', $student_id)
                ->where('student_record_id', $record_id)->get();
        }

        return SmStudentTakeOnlineExam::where('active_status', 1)->where('status', 2)
            ->where('academic_id', getAcademicId())
            ->where('student_id', $student_id)
            ->where('school_id', Auth::user()->school_id)
            ->get();

    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withDefault()->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function admitcard()
    {
        return $this->belongsTo(AdmitCard::class, 'student_record_id');
    }

    public function section()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(\App\SmSection::class, 'un_section_id', 'id')->withDefault()->withoutGlobalScope(StatusAcademicSchoolScope::class);
        }

        return $this->belongsTo(\App\SmSection::class, 'section_id', 'id')->withDefault()->withoutGlobalScope(StatusAcademicSchoolScope::class);

    }

    public function smClass()
    {
        return $this->belongsTo(SmClass::class, 'class_id');
    }

    public function unSection()
    {
        return $this->belongsTo(\App\SmSection::class, 'un_section_id', 'id')->withDefault()->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function student()
    {
        return $this->hasOne(\App\SmStudent::class, 'id', 'student_id');
    }

    public function saasstudent()
    {
        return $this->hasOne(\App\SmStudent::class, 'id', 'student_id')->withOutGlobalScope(SchoolScope::class);
    }

    public function school()
    {
        return $this->belongsTo(\App\SmSchool::class, 'school_id', 'id')->withDefault();
    }

    public function academic()
    {
        return $this->belongsTo(\App\SmAcademicYear::class, 'academic_id', 'id')->withDefault();
    }

    public function classes()
    {
        return $this->hasMany(SmClass::class, 'academic_id', 'academic_id');
    }

    public function studentDetail()
    {
        return $this->belongsTo(\App\SmStudent::class, 'student_id', 'id')->withDefault();
    }

    public function fees()
    {
        return $this->hasMany(SmFeesAssign::class, 'record_id', 'id');
    }

    public function feesDiscounts()
    {
        return $this->hasMany(SmFeesAssignDiscount::class, 'record_id', 'id');
    }

    public function homework()
    {
        return $this->hasMany(SmHomework::class, 'record_id', 'id')->whereNull('course_id')->whereNull('chapter_id')->whereNull('lesson_id');
    }

    public function saashomeworks()
    {
        return $this->hasMany(SmHomework::class, 'record_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function studentAttendance()
    {
        return $this->hasMany(SmStudentAttendance::class, 'student_record_id', 'id');
    }

    public function studentAttendanceByMonth(string $month, string $year)
    {
        return $this->studentAttendance()->where('attendance_date', 'like', $year.'-'.$month.'%')->get();
    }

    public function getLessonPlanAttribute()
    {
        return LessonPlanner::where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->where('active_status', 1)
            ->distinct('lesson_detail_id')
            ->get();
    }

    public function getHomeWorkAttribute()
    {
        $shift_id = shiftEnable() ? $this->shift_id:null;
        return SmHomework::with('classes', 'sections', 'subjects', 'shift')->where('class_id', $this->class_id)->where('section_id', $this->section_id)
           ->when(shiftEnable() && !empty($shift_id), function($query) use($shift_id){
                $query->where('shift_id',$shift_id);
            })
            ->whereNull('course_id')
            ->where('sm_homeworks.academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
    }

    public function getParentPanelHomeWorkAttribute()
    {
        return SmHomework::with('classes', 'sections', 'subjects')->where('class_id', $this->class_id)->where('section_id', $this->section_id)
            ->whereNull('course_id')
            ->where('sm_homeworks.academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('evaluation_date', '=', null)
                ->where('submission_date', '>', Carbon::now())->get();
    }

    public function getUploadContent($type, $is_university = null)
    {
        if ($is_university == null) {
            $class = $this->class_id;
            $section = $this->section_id;
            $shift = shiftEnable() ? $this->shift_id:null;

            return SmTeacherUploadContent::where('content_type', $type)
                ->where(function ($que) use ($class) {
                    return $que->where('class', $class)
                        ->orWhereNull('class');
                })
                ->where(function ($que) use ($section) {
                    return $que->where('section', $section)
                        ->orWhereNull('section');
                })
                ->when(shiftEnable() && !empty($shift), function ($que) use ($shift) {
                    return $que->where('shift_id', $shift);
                })
                ->where('course_id', '=', null)
                ->where('chapter_id', '=', null)
                ->where('lesson_id', '=', null)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
        }

        $un_semester_label_id = $this->un_semester_label_id;
        $section_id = $this->un_section_id;

        return SmTeacherUploadContent::where('content_type', $type)
            ->where(function ($que) use ($un_semester_label_id) {
                return $que->where('un_semester_label_id', $un_semester_label_id);
            })
            ->where(function ($que) use ($section_id) {
                return $que->where('un_section_id', $section_id);
            })
            ->where('course_id', '=', null)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

    }

    public function homeworkContents($is_university = null)
    {
        if ($is_university === null) {
            
            $class = $this->class_id;
            $section = $this->section_id;
            $shift = shiftEnable() ? $this->shift_id:null;

            return SmHomework::where('school_id', auth()->user()->school_id)
                ->where(function ($que) use ($class) {
                    return $que->where('class_id', $class)
                        ->orWhereNull('class_id');
                })
                ->where(function ($que) use ($section) {
                    return $que->where('section_id', $section)
                        ->orWhereNull('section_id');
                })
                ->when(shiftEnable() && !empty($shift),function($que) use ($shift){
                    return $que->where('shift_id', $shift);
                })
                ->whereNull('course_id')
                ->whereNull('chapter_id')
                ->whereNull('lesson_id')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();
        }

        $un_semester_label_id = $this->un_semester_label_id;
        $section_id = $this->un_section_id;

        return SmHomework::where('school_id', auth()->user()->school_id)
            ->where(function ($que) use ($un_semester_label_id) {
                return $que->where('un_semester_label_id', $un_semester_label_id);
            })
            ->where(function ($que) use ($section_id) {
                return $que->where('un_section_id', $section_id);
            })
            ->where('course_id', '=', null)
            ->where('un_academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->get();

    }

    public function getExamAttribute()
    {
        return SmExam::with('examType')->where('class_id', $this->class_id)->where('section_id', $this->section_id)->where('school_id', Auth::user()->school_id)->where('academic_id', getAcademicId())->where('active_status', 1)->get();
    }

    public function getAssignSubjectAttribute()
    {   
        $shift_id = shiftEnable() ? $this->shift_id:null;
        return SmAssignSubject::where('class_id', $this->class_id)->where('section_id', $this->section_id)
                             ->where('academic_id', $this->academic_id)
                             ->when(shiftEnable(),function($query) use($shift_id){
                                return $query->where('shift_id',$shift_id);
                             })
                             ->where('school_id', Auth::user()->school_id)
                             ->get();
    }

    public function getUnAssignSubjectAttribute()
    {
        return UnAssignSubject::where('un_semester_label_id', $this->un_semester_label_id)->where('school_id', Auth::user()->school_id)->get();
    }

    public function getOnlineExamAttribute()
    {
        // SmAssignSubject::where('class_id', $this->class_id)
        //     ->where('section_id', $this->section_id)->where('school_id', Auth::user()->school_id)
        //     ->where('academic_id', getAcademicId())
        //     ->where('shift_id', $this->shift_id)
        //     ->pluck('subject_id')->unique();
        if (moduleStatusCheck('OnlineExam') === true) {
            if (moduleStatusCheck('University')) {
                return InfixOnlineExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('status', 1)
                    ->where('un_faculty_id', $this->un_faculty_id)
                    ->where('un_department_id', $this->un_department_id)
                    ->where('un_semester_label_id', $this->un_semester_label_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }

            return InfixOnlineExam::with('studentSubmitExamWithStatus')
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('status', 1)
                    ->where('class_id', $this->class_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get()
                    ->filter(function ($exam) {
                        return !$exam->section_id || $exam->section_id == $this->section_id;
                    });
        }
        $shift_id = shiftEnable() ? $this->shift_id:null;
        return SmOnlineExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('status', 1)->where('class_id', $this->class_id)->where('section_id', $this->section_id)
                    ->when(shiftEnable() && !empty($shift_id), function($query) use ($shift_id){
                        $query->where('shift_id',$shift_id);
                    })
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
    }

    public function getInfixStudentTakeOnlineExamAttribute()
    {
        if (moduleStatusCheck('OnlineExam') === true && auth()->user()->role_id === 2) {
            return InfixStudentTakeOnlineExam::where('status', 2)
                ->where('student_id', auth()->user()->student->id)
                ->whereHas('onlineExam', function ($query) {
                    return $query->when(moduleStatusCheck('Lms'), function ($q): void {
                        $q->whereNull('course_id');
                    });
                })->where('student_record_id', $this->id)->get();
        }

        return null;
    }

    public function getStudentTeacherAttribute()
    {
        $shift_id = shiftEnable() &&  !empty($this->shift_id) ? $this->shift_id:null;
        return SmAssignSubject::with('teacher', 'subject')->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)->distinct('teacher_id')->where('academic_id', getAcademicId())
            ->when(shiftEnable() && !empty($shift_id),function($query) use ($shift_id){
                return $query->where('shift_id',$shift_id);
            })
            ->where('school_id', Auth::user()->school_id)
            ->get();
    }

    public function getStudentTeacherCount()
    {
        $shift_id = shiftEnable() ? $this->shift_id:null;
        return SmAssignSubject::with('teacher', 'subject')
            ->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->where('academic_id', getAcademicId())
            ->when(shiftEnable() && !empty($shift_id),function($query) use ($shift_id){
                return $query->where('shift_id',$shift_id);
            })
            ->where('school_id', Auth::user()->school_id)
            ->get()
            ->unique('teacher_id')
            ->count();
    }

    public function getStudentVirtualClassAttribute()
    {
        return VirtualClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            ->where('school_id', Auth::user()->school_id)
            ->get();
    }

    public function getStudentInappliveClassAttribute()
    {
        return InAppLiveClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            ->where('school_id', auth()->user()->school_id)
            ->get();
    }

    public function getUnstudentVirtualClassAttribute()
    {
        return VirtualClass::where('un_semester_label_id', $this->un_semester_label_id)
            ->where(function ($q) {
                return $q->where('un_section_id', $this->un_section_id)->orWhereNull('un_section_id');
            })
            ->where('school_id', Auth::user()->school_id)
            ->get();
    }

    public function getStudentBbbVirtualClassAttribute()
    {
        return BbbVirtualClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            // ->where('school_id', Auth::user()->school_id)
            ->get();
    }

    public function getStudentBbbVirtualClassRecordAttribute()
    {
        $meetings = BbbVirtualClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            // ->where('school_id', Auth::user()->school_id)
            ->get();
        $meeting_id = $meetings->pluck('meeting_id')->toArray();

        return Bigbluebutton::getRecordings(['meetingID' => $meeting_id]);
    }

    public function getStudentJitsiVirtualClassAttribute()
    {
        return JitsiVirtualClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            ->get();
    }

    public function getStudentGmeetVirtualClassAttribute()
    {
        return GmeetVirtualClass::where('class_id', $this->class_id)
            ->where(function ($q) {
                return $q->where('section_id', $this->section_id)->orWhereNull('section_id');
            })
            ->get();
    }

    public function getOnlineWrittenExamAttribute()
    {
        if (moduleStatusCheck('University')) {
            return InfixWrittenExam::where('active_status', 1)->where('un_academic_id', getAcademicId())->where('status', 1)->where('un_semester_label_id', $this->un_semester_label_id)->where('school_id', Auth::user()->school_id)->get();
        }

        return InfixWrittenExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('status', 1)->where('class_id', $this->class_id)->where('section_id', $this->section_id)->where('school_id', Auth::user()->school_id)->with('class', 'section', 'subject')->get();

    }

    public function getStudentCoursesAttribute()
    {
        // return Course::where(function ($q) {
        //     return $q->where('class_id', $this->class_id)->orWhere('class_id', null)->orWhere('class_id', 0);
        // })->where(function ($q) {
        //     return $q->where('section_id', $this->section_id)->orWhere('section_id', null);
        // })->withCount('chapters', 'lessons')->where('active_status', 1)->where('publish', 1)->get();

        return Course::where(function ($q) {
            return $q->where('courses.class_id', $this->class_id)
                ->orWhere('courses.class_id', null)
                ->orWhere('courses.class_id', 0);
        })
            ->where(function ($q) {
                return $q->where('courses.section_id', $this->section_id)
                    ->orWhere('courses.section_id', null);
            })
            ->where('courses.active_status', 1)
            ->where('courses.publish', 1)
            ->with(['category', 'subCategory'])
            ->withCount(['chapters', 'lessons'])
            ->leftJoin('course_categories as parent', 'courses.category_id', '=', 'parent.id')
            ->leftJoin('course_categories as child', 'courses.sub_category_id', '=', 'child.id')
            ->orderByRaw('
            COALESCE(parent.position_order, 9999),
            COALESCE(child.position_order, 9999),
            courses.id
        ')
            ->select('courses.*')
            ->get();
    }

    public function feesInvoice()
    {
        return $this->hasMany(\Modules\Fees\Entities\FmFeesInvoice::class, 'record_id', 'id');
    }

    public function unSession()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSession::class, 'un_session_id', 'id')->withDefault();
    }

    public function unFaculty()
    {
        return $this->belongsTo(\Modules\University\Entities\UnFaculty::class, 'un_faculty_id', 'id')->withDefault();
    }

    public function unDepartment()
    {
        return $this->belongsTo(\Modules\University\Entities\UnDepartment::class, 'un_department_id', 'id')->withDefault();
    }

    public function unAcademic()
    {
        return $this->belongsTo(\Modules\University\Entities\UnAcademicYear::class, 'un_academic_id', 'id')->withDefault();
    }

    public function unSemester()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function unSemesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function semester()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function markStoreDetails()
    {
        return $this->belongsTo(\App\SmMarkStore::class, 'student_record_id', 'id')->withDefault();
    }

    public function unStudentSubjects()
    {
        return $this->hasMany(UnSubjectAssignStudent::class, 'student_record_id', 'id');
    }

    public function unStudentSemesterWiseSubjects()
    {
        return $this->hasMany(UnSubjectAssignStudent::class, 'student_record_id', 'id')
            ->where('un_semester_label_id', $this->un_semester_label_id)
            ->orderby('un_semester_label_id', 'DESC');
    }

    public function unStudentRequestSubjects()
    {
        return $this->hasMany(\Modules\University\Entities\RequestSubject::class, 'student_record_id', 'id')
            ->where('un_semester_label_id', $this->un_semester_label_id)
            ->orderby('un_semester_label_id', 'DESC');
    }

    public function feesInstallments()
    {
        return $this->hasMany(\Modules\University\Entities\UnFeesInstallmentAssign::class, 'record_id', 'id');
    }

    public function directFeesInstallments()
    {
        return $this->hasMany(DirectFeesInstallmentAssign::class, 'record_id', 'id');
    }

    public function getWithOutPreSubjectAttribute()
    {
        $preSubjectIds = UnSubjectPreRequisite::pluck('un_subject_id')->toArray();
        $assignSubjects = [];

        UnSubjectAssignStudent::where('un_semester_label_id', $this->un_semester_label_id)
            ->where('student_id', $this->student_id)
            ->where('student_record_id', $this->id)
            ->pluck('un_subject_id')
            ->toArray();

        $completeSubjects = UnSubjectComplete::where('student_id', $this->student_id)
            // ->where('is_pass', '!=', 'pass')
            ->pluck('un_subject_id')->toArray();
        $array = array_unique(array_merge($preSubjectIds, $assignSubjects, $completeSubjects));

        return UnSubject::where('un_faculty_id', $this->un_faculty_id)
            ->where('un_department_id', $this->un_department_id)
            ->whereNotIn('id', $array)
            ->where('school_id', auth()->user()->school_id)
            ->orWhereNull('un_department_id')
            ->orWhereNull('un_faculty_id')
            ->get();
    }

    public function getStudentNameAttribute()
    {
        return $this->studentDetail ? $this->studentDetail->full_name : '';
    }

    public function getRollNoAttribute($value)
    {
        if (generalSetting()->multiple_roll) {
            return $value;
        }

        $this->load('studentDetail');

        return $this->studentDetail->roll_no;
    }

    public function credit()
    {
        return $this->hasOne(FeesInstallmentCredit::class, 'student_record_id');
    }

    public function alumni()
    {
        return $this->hasOne(\Modules\Alumni\Entities\Alumni::class, 'record_id');
    }
    // public function examSchedule()
    // {
    //     return $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')->where('section_id', $this->section_id)->where('shift_id',$this->shift_id);
    // }
    public function examSchedule()
    {
        $query = $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')
                    ->where('section_id', $this->section_id);
        if (shiftEnable()) {
            $query->where('shift_id', $this->shift_id);
        }

        return $query;
        $shift_id = !empty($this->shift_id) ? $this->shift_id:null;
        return $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')
                        ->where('section_id', $this->section_id)
                        ->when(shiftEnable() && !empty($shift_id), function($query) use ($shift_id){
                            $query->where('shift_id',$shift_id);
                        });
    }

    public function incidents()
    {
        return $this->hasMany(AssignIncident::class, 'record_id', 'id');
    }

    public function teacherEvaluation()
    {
        return $this->hasMany(TeacherEvaluation::class, 'record_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
