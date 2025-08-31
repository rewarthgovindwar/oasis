<?php

namespace App;

use Exception;
use Carbon\Carbon;
use App\Scopes\SchoolScope;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\FeesInstallmentCredit;
use Illuminate\Database\Eloquent\Model;
use Modules\OnlineExam\Entities\InfixPdfExam;
use Modules\OnlineExam\Entities\InfixOnlineExam;
use Modules\University\Entities\UnSubjectComplete;
use Modules\FeesCollection\Entities\InfixFeesMaster;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\FeesCollection\Entities\InfixFeesPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FeesCollection\Entities\InfixAssignDiscount;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;
use Modules\OnlineExam\Entities\InfixStudentTakeWrittenExam;

class SmStudent extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'user_id' => 'integer',
        'role_id' => 'integer',
        'admission_no' => 'integer',
        'roll_no' => 'integer',
        'age' => 'integer',
        'bloodgroup_id' => 'integer',
        'religion_id' => 'integer',
        'parent_id' => 'integer',
        'route_list_id' => 'integer',
        'vechile_id' => 'integer',
        'dormitory_id' => 'integer',
        'height' => 'double',
        'weight' => 'double',
        'school_id' => 'integer',
        'gender_id' => 'integer',
        'academic_id' => 'integer',
        'active_status' => 'integer',
    ];

    public static function totalFees($feesAssigns)
    {
        try {
            $amount = 0;
            foreach ($feesAssigns as $feeAssign) {
                $master = SmFeesMaster::select('fees_group_id', 'amount', 'date')->where('id', $feeAssign->fees_master_id)->first();

                $due_date = strtotime($master->date);
                $now = strtotime(date('Y-m-d'));
                if ($due_date > $now) {
                    continue;
                }

                $amount += $master->amount;
            }

            return $amount;
        } catch (Exception $exception) {
            return [0 => $exception->getMessage()];
        }
    }

    public static function totalDeposit($feesAssigns, $student_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feeAssign) {
                $fees_type = SmFeesMaster::select('fees_type_id')->where('id', $feeAssign->fees_master_id)->first();
                $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $fees_type->fees_type_id)->where('student_id', $student_id)->sum('amount');
            }

            return $amount;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function totalDiscount($feesAssigns, $student_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feeAssign) {
                $amount = SmFeesAssign::where('student_id', $student_id)->sum('applied_discount');
            }

            return $amount;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function totalFine($feesAssigns, $student_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feeAssign) {
                $fees_type = SmFeesMaster::select('fees_type_id')->where('id', $feeAssign->fees_master_id)->first();
                $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $fees_type->fees_type_id)->where('student_id', $student_id)->sum('fine');
            }

            return $amount;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function marks($exam_id, $s_id)
    {

        try {
            $marks_register = SmMarksRegister::where('exam_id', $exam_id)->where('student_id', $s_id)->first();
            if ($marks_register !== '') {
                return SmMarksRegisterChild::where('marks_register_id', $marks_register->id)->where('active_status', 1)->get();
            }

            return [];
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function fullMarks($exam_id, $sb_id)
    {
        try {
            return SmExamScheduleSubject::where('exam_schedule_id', $exam_id)->where('subject_id', $sb_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function fullMarksBySubject($exam_id, $sb_id)
    {
        try {
            return SmExamSetup::where('exam_term_id', $exam_id)->where('subject_id', $sb_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_fullMarksBySubject($exam_id, $sb_id, $request)
    {
        try {
            $SmExamSetup = SmExamSetup::query();

            return universityFilter($SmExamSetup, $request)
                ->where('exam_term_id', $exam_id)
                ->where('un_subject_id', $sb_id)
                ->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function un_scheduleBySubject($exam_id, $sb_id, $request)
    {
        try {
            $SmExamSchedule = SmExamSchedule::query();

            return universityFilter($SmExamSchedule, $request)
                ->where('exam_term_id', $exam_id)
                ->where('un_subject_id', $sb_id)
                ->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function scheduleBySubject($exam_id, $sb_id, $record)
    {
        try {
            return SmExamSchedule::where('exam_term_id', $exam_id)
                ->where('subject_id', $sb_id)
                ->where('class_id', $record->class_id)
                ->where('section_id', $record->section_id)
                ->first();
        } catch (Exception $exception) {
            return null;
        }
    }

    public static function classPromote($class)
    {
        try {
            $class = SmClass::where('id', $class)->first();

            return $class->class_name;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function sessionPromote($session)
    {
        try {
            $session = SmSession::where('id', $session)->first();

            return $session->session;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getExamResult($exam_id, $record)
    {
        $eligible_subjects = SmAssignSubject::withOutGlobalScopes()
            ->where('class_id', $record->class_id)
            ->where('section_id', $record->section_id)
            ->where('academic_id', SmAcademicYear::API_ACADEMIC_YEAR($record->school_id))
            ->where('school_id', Auth::user()->school_id)
            ->select('subject_id')
            ->distinct(['section_id', 'subject_id'])
            ->get();

        foreach ($eligible_subjects as $eligible_subject) {
            $getMark = SmResultStore::withOutGlobalScopes()->where([
                ['exam_type_id', $exam_id],
                ['student_id', $record->student_id],
                ['student_record_id', $record->id],
                ['subject_id', $eligible_subject->subject_id],
            ])->first();

            if ($getMark == '') {
                continue;
            }

            return SmResultStore::withOutGlobalScopes()->where([
                ['exam_type_id', $exam_id],
                ['student_id', $record->student_id],
                ['student_record_id', $record->id],
            ])->get();
        }

        return [];
    }

    public static function un_getExamResult($exam_id, $record, $request)
    {
        $builder = SmExamSetup::query();
        $eligible_subjects = universityFilter($builder, $request)
            ->where('exam_term_id', $exam_id)
            ->get();

        foreach ($eligible_subjects as $eligible_subject) {
            $SmResultStore = SmResultStore::query();
            $getMark = universityFilter($SmResultStore, $request)
                ->where([
                    ['exam_type_id', $exam_id],
                    ['student_id', $record->student_id],
                    ['student_record_id', $record->id],
                    ['un_subject_id', $eligible_subject->un_subject_id],
                ])->first();

            if ($getMark == '') {
                return false;
            }

            $SmResultStore = SmResultStore::query();

            return universityFilter($SmResultStore, $request)
                ->where([
                    ['exam_type_id', $exam_id],
                    ['student_id', $record->student_id],
                    ['student_record_id', $record->id],
                ])->get();
        }

        return null;
    }

    public function parents()
    {
        return $this->belongsTo(SmParent::class, 'parent_id', 'id')->withDefault()->with('parent_user')->withOutGlobalScope(SchoolScope::class);
    }

    public function getOptionalSubjectSetupAttribute()
    {
        return SmClassOptionalSubject::where('class_id', $this->class_id)->first();
    }

    public function optionalSubject()
    {
        return $this->belongsTo(SmOptionalSubjectAssign::class, 'student_id', 'id');
    }

    public function drivers()
    {
        return $this->belongsTo(SmStaff::class, 'driver_id', 'id');
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function feesPayment()
    {
        return $this->hasMany(SmFeesPayment::class, 'student_id');
    }

    public function gender()
    {
        return $this->belongsTo(SmBaseSetup::class, 'gender_id', 'id')->withDefault();
    }

    public function school()
    {
        return $this->belongsTo(SmSchool::class, 'school_id', 'id');
    }

    public function religion()
    {
        return $this->belongsTo(SmBaseSetup::class, 'religion_id', 'id')->withDefault();
    }

    public function bloodGroup()
    {
        return $this->belongsTo(SmBaseSetup::class, 'bloodgroup_id', 'id')->withDefault();
    }

    public function category()
    {
        return $this->belongsTo(SmStudentCategory::class, 'student_category_id', 'id')->withDefault();
    }

    public function group()
    {
        return $this->belongsTo(SmStudentGroup::class, 'student_group_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(SmSession::class, 'session_id', 'id');
    }

    public function academicYear()
    {
        return $this->belongsTo(SmAcademicYear::class, 'academic_id', 'id');
    }

    // student class name
    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withoutGlobalScopes();
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withoutGlobalScopes();
    }

    public function route()
    {
        return $this->belongsTo(SmRoute::class, 'route_list_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo(SmVehicle::class, 'vechile_id', $this->vehicle_id);
    }

    public function dormitory()
    {
        return $this->belongsTo(SmDormitoryList::class, 'dormitory_id', 'id');
    }

    public function sections()
    {
        return $this->hasManyThrough(SmSection::class, SmClassSection::class, 'class_id', 'id', 'class_id', 'section_id');
    }

    public function rooms()
    {
        return $this->hasMany(SmRoomList::class, 'dormitory_id', 'dormitory_id');
    }

    public function room()
    {
        return $this->belongsTo(SmRoomList::class, 'room_id', 'id');
    }

    public function attendances()
    {
        return $this->hasMany(SmStudentAttendance::class, 'student_id');
    }

    public function forwardBalance()
    {
        return $this->belongsTo(SmFeesCarryForward::class, 'id', 'student_id');
    }

    public function meritList()
    {
        return $this->belongsTo(SmTemporaryMeritlist::class, 'id', 'student_id');
    }

    public function feesAssign()
    {
        return $this->hasMany(SmFeesAssign::class, 'student_id', 'id')->where('academic_id', getAcademicId());
    }

    public function feesAssignDiscount()
    {
        return $this->hasMany(SmFeesAssignDiscount::class, 'student_id', 'id')->where('academic_id', getAcademicId());
    }

    public function studentDocument()
    {
        return $this->hasMany(SmStudentDocument::class, 'student_staff_id', 'id')->where('academic_id', getAcademicId());
    }

    public function studentTimeline()
    {
        return $this->hasMany(SmStudentTimeline::class, 'staff_student_id', 'id');
    }

    public function studentLeave()
    {
        return $this->hasMany(SmLeaveRequest::class, 'staff_id', $this->user_id)->where('role_id', 2);
    }

    public function getClass()
    {
        return $this->belongsTo(CheckClass::class, 'class_id');
    }

    public function getAttendanceType($month)
    {
        return $this->attendances()->whereMonth('attendance_date', $month)->get();
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    public function assignDiscount()
    {
        return $this->hasMany(InfixAssignDiscount::class, 'student_id');
    }

    public function feesMasters()
    {
        return $this->hasMany(InfixFeesMaster::class, 'class_id', 'class_id');
    }

    public function markStores()
    {
        return $this->hasMany(SmMarkStore::class, 'student_id')->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id);
    }

    public function assignSubjects()
    {
        return $this->hasMany(SmAssignSubject::class, 'class_id', 'class_id')->where('section_id', $this->section_id)->where('active_status', 1);
    }

    public function studentOnlineExams()
    {

        if (moduleStatusCheck('OnlineExam') == true) {
            return $this->hasMany(InfixOnlineExam::class, 'class_id', 'class_id')->where('section_id', $this->section_id)
                ->where('active_status', 1)->where('status', 1)->where('school_id', Auth::user()->school_id);
        }

        return $this->hasMany(SmOnlineExam::class, 'class_id', 'class_id')->where('section_id', $this->section_id)
            ->where('active_status', 1)->where('status', 1)->where('school_id', Auth::user()->school_id);

    }

    public function studentPdfExams()
    {
        return $this->hasMany(InfixPdfExam::class, 'class_id', 'class_id')->where('section_id', $this->section_id)
            ->where('active_status', 1)->where('status', 1)->where('school_id', Auth::user()->school_id);
    }

    public function scheduleBySubjects()
    {
        return $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')
            ->where('section_id', $this->section_id);
    }

    public function assignSubject()
    {
        return $this->hasMany(SmAssignSubject::class, 'class_id', 'class_id')->where('section_id', $this->section_id)->distinct('teacher_id');
    }

    public function bookIssue()
    {
        return $this->hasMany(SmBookIssue::class, 'member_id', 'user_id')->where('issue_status', 'I');
    }

    public function examSchedule()
    {
        return $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')->where('section_id', $this->section_id);
    }

    public function homework()
    {
        return $this->hasMany(SmHomework::class, 'class_id', 'class_id')->where('section_id', $this->section_id)
            ->where('evaluation_date', '=', null)->where('submission_date', '>', date('Y-m-d'));
    }

    public function studentAttendances()
    {
        return $this->hasMany(SmStudentAttendance::class, 'student_id')->where('attendance_date', 'like', date('Y').'-'.date('m').'%')
            ->where('attendance_type', 'P');
    }

    public function studentOnlineExam()
    {
        if (moduleStatusCheck('OnlineExam') == true) {
            return $this->hasMany(InfixStudentTakeOnlineExam::class, 'student_id');
        }

        return $this->hasMany(SmStudentTakeOnlineExam::class, 'student_id');

    }

    public function studentWrittenExams()
    {
        if (moduleStatusCheck('OnlineExam') == true) {
            return $this->hasMany(InfixStudentTakeWrittenExam::class, 'student_id');
        }

        return null;
    }

    public function examsSchedule()
    {
        return $this->hasMany(SmExamSchedule::class, 'class_id', 'class_id')->where('section_id', $this->section_id);
    }

    public function homeworkContents()
    {
        return $this->hasMany(SmUploadHomeworkContent::class, 'student_id');
    }

    public function bankSlips()
    {
        return $this->hasMany(SmBankPaymentSlip::class, 'student_id');
    }

    public function getTotalAmount()
    {
        $amount = 0;
        foreach ($this->feesAssign as $feeAssign) {
            $amount += $feeAssign->feesGroupMaster->amount;
        }

        return $amount;
    }

    public function getTotalDiscount($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feeAssign) {
            $amount += SmFeesAssign::where('fees_type_id', $feeAssign->feesGroupMaster->fees_type_id)->where('student_id', $id)->sum('discount_amount');
        }

        return $amount;
    }

    public function getTotalFine($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feeAssign) {
            $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $feeAssign->feesGroupMaster->fees_type_id)->where('student_id', $id)->sum('fine');
        }

        return $amount;
    }

    public function getTotalDeposit($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feeAssign) {
            $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $feeAssign->feesGroupMaster->fees_type_id)->where('student_id', $id)->sum('amount');
        }

        return $amount;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function promotion()
    {
        return $this->hasMany(SmStudentPromotion::class, 'student_id', 'id');
    }

    public function feesPayments()
    {
        return $this->hasMany(InfixFeesPayment::class, 'student_id');
    }

    public function getClassesAttribute(): string
    {
        if (count($this->promotion) > 0) {
            $maxClass = $this->promotion->max('current_class_id');
            $minClass = $this->promotion->min('previous_class_id');

            return $minClass.' - '.$maxClass;
        }

        return $this->class->class_name.' - '.$this->class->class_name;
    }

    public function getSessionsAttribute(): string
    {
        $sessions = '';
        if (count($this->promotion) > 0) {
            $maxSession = $this->promotion->max('current_session_id');
            $minSession = $this->promotion->min('previous_session_id');
            $maxYear = SmAcademicYear::find($maxSession)->year ?? '';
            $minYear = SmAcademicYear::find($minSession)->year ?? '';
            $sessions = $minYear.' - '.$maxYear;
        } else {
            @$sessions = $this->academicYear->year.' - '.$this->academicYear->year;
        }

        return $sessions;
    }

    public function examAttendances()
    {
        return $this->hasMany(SmExamAttendanceChild::class, 'student_id');
    }

    public function homeworks()
    {
        return $this->hasMany(SmHomeworkStudent::class, 'student_id');
    }

    public function onlineExams()
    {
        return $this->hasMany(SmStudentTakeOnlineExam::class, 'student_id');
    }

    public function subjectAssign()
    {
        return $this->hasOne(SmOptionalSubjectAssign::class, 'student_id')->where('academic_id', getAcademicId());
    }

    public function subjectAssigns()
    {
        return $this->hasMany(SmOptionalSubjectAssign::class, 'student_id')
            ->where('academic_id', getAcademicId())
            ->latest('id');
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('school_id', Auth::user()->school_id);
    }

    public function DateWiseAttendances()
    {
        if (moduleStatusCheck('Univeristy')) {
            $request = request();

            return $this->hasOne(SmStudentAttendance::class, 'student_id')
                ->when($request->un_session_id, function ($q) use ($request): void {
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
                })->where('school_id', auth()->user()->school_id)
                ->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        }

        return $this->hasOne(SmStudentAttendance::class, 'student_id')
            ->when(request()->class_id, function ($q): void {
                $q->where('class_id', request()->class_id);
            }, function ($elseQ): void {
                $elseQ->where('class_id', request()->class);
            })->when(request()->section_id, function ($q): void {
                $q->where('section_id', request()->section_id);
            }, function ($elseQ): void {
                $elseQ->where('section_id', request()->class);
            })->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
    }

    public function DateSubjectWiseAttendances()
    {
        if (moduleStatusCheck('University')) {
            return $this->hasOne(SmSubjectAttendance::class, 'student_id')->where('un_semester_label_id', request()->un_semester_label_id)->where('un_subject_id', request()->un_subject_id)->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        }

        return $this->hasOne(SmSubjectAttendance::class, 'student_id')->where('class_id', request()->class)->where('section_id', request()->section)->where('subject_id', request()->subject)->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));

    }

    public function lead()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo(\Modules\Lead\Entities\Lead::class, 'lead_id', 'id')->withDefault();
        }

        return null;
    }

    public function leadCity()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo(\Modules\Lead\Entities\LeadCity::class, 'lead_city_id', 'id')->withDefault();
        }

        return null;
    }

    public function source()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo(\Modules\Lead\Entities\Source::class, 'source_id', 'id')->withDefault();
        }

        return null;
    }

    public function allRecords()
    {
        return $this->hasMany(StudentRecord::class, 'student_id', 'id')->orderBy('id', 'DESC');
    }

    public function studentAllRecords()
    {
        return $this->hasMany(StudentRecord::class, 'student_id', 'id')->where('is_promote', 0)->orderBy('id', 'DESC');
    }

    public function studentRecords()
    {
        return $this->hasMany(StudentRecord::class, 'student_id', 'id')->where('is_promote', 0)->where('active_status', 1);
    }

    public function orderByStudentRecords()
    {
        return $this->hasMany(StudentRecord::class, 'student_id', 'id')->where('is_promote', 0)->where('active_status', 1)->orderBy('id', 'DESC');
    }

    public function getClassRecord()
    {
        return $this->hasMany(StudentRecord::class, 'student_id', 'id')->where('is_promote', 0)->distinct('class_id');
    }

    public function studentRecord()
    {
        return auth()->check() ? $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)
            ->when(moduleStatusCheck('University') == false, function ($q): void {
                $q->where('academic_id', getAcademicId());
            })->where('school_id', Auth::user()->school_id) : $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)
            ->when(moduleStatusCheck('University') == false, function ($q): void {
                $q->where('academic_id', getAcademicId());
            })->where('school_id', app('school')->id);
    }

    public function defaultClass()
    {
        return $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)->latest()->where('is_default', 1)
            ->when(moduleStatusCheck('University'), function ($query): void {
                $query->where('un_academic_id', getAcademicId());
            }, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })->where('school_id', Auth::user()->school_id);
    }

    public function recordClass()
    {
        return $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)->where('class_id', request()->class)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);
    }

    public function recordClassSaas()
    {
        return $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)->where('class_id', request()->class)->withOutGlobalScope(SchoolScope::class);
    }

    public function recordSection()
    {
        return $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)->where('class_id', request()->class)->where('section_id', request()->section)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);
    }

    public function recordClasses()
    {
        return $this->hasMany(StudentRecord::class, 'student_id')->where('is_promote', 0)->where('class_id', request()->class)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id);
    }

    public function recordStudentRoll()
    {
        return $this->hasOne(StudentRecord::class, 'student_id')->where('is_promote', 0)->where('class_id', request()->current_class)->where('section_id', request()->current_section)->where('academic_id', request()->current_session)->where('school_id', Auth::user()->school_id);
    }

    public function completeSubjects()
    {
        return $this->hasMany(UnSubjectComplete::class, 'student_id', 'id')->where('is_pass', 'pass');
    }

    public function lastRecord()
    {
        return $this->hasOne(StudentRecord::class, 'student_id', 'id')->where('is_promote', 0)->latest();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getRollNoAttribute($value): ?int
    {
        if (generalSetting()->multiple_roll) {
            $this->load('recordClass');
            if ($this->recordClass) {
                return (int) $this->recordClass->roll_no;
            }
        } else {
            return (int) $this->getRawOriginal('roll_no');
        }

        return null;
    }

    public function feesCredits()
    {
        return $this->hasMany(FeesInstallmentCredit::class, 'student_id');
    }

    public function alumni()
    {
        return $this->hasOne(\Modules\Alumni\Entities\Alumni::class, 'student_id', 'id');
    }

    public function incidents()
    {
        return $this->hasMany(AssignIncident::class, 'student_id', 'id');
    }

    public function getIncidentsSumPointAttribute()
    {
        return ($this->incidents_sum_point_1 ?? 0) - ($this->incidents_sum_point_2 ?? 0);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
