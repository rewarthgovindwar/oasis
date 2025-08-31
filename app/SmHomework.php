<?php

namespace App;

use Exception;
use App\Models\StudentRecord;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnSemesterLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\University\Entities\UnSemesterLabelAssignSection;

class SmHomework extends Model
{
    use HasFactory;

    protected $table = 'sm_homeworks';

    protected $fillable = [
        'class_id', 'section_id', 'subject_id', 'created_by', 'evaluated_by',
    ];

    protected $appends = ['HomeworkPercentage'];

    protected $casts = [
        'id' => 'integer',
        'homework_date' => 'string',
        'submission_date' => 'string',
        'evaluation_date' => 'string',
        // 'file' => 'string',
        'file'=> 'array',
        'marks' => 'string',
        'description' => 'string',
        'active_status' => 'integer',
        'created_at' => 'string',
        'updated_at' => 'string',
        'evaluated_by' => 'integer',
        'class_id' => 'string',
        'record_id' => 'integer',
        'section_id' => 'string',
        'subject_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'school_id' => 'integer',
        'academic_id' => 'integer',
        'course_id' => 'integer',
        'lesson_id' => 'integer',
        'chapter_id' => 'integer',
    ];

    public static function getHomeworkPercentage($class_id, $section_id, $homework_id)
    {
        try {
            $totalStudents = StudentRecord::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->count();
            $totalHomeworkCompleted = SmHomeworkStudent::select('id')
                ->where('homework_id', $homework_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();

            if (isset($totalStudents)) {
                return [
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                ];
            }

            return false;

        } catch (Exception $exception) {
            return false;
        }
    }

    public static function evaluationHomework($s_id, $h_id)
    {

        try {
            return SmHomeworkStudent::withOutGlobalScopes()->where('homework_id', $h_id)->where('student_id', $s_id)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function uploadedContent($s_id, $h_id)
    {
        try {
            return SmUploadHomeworkContent::where('homework_id', $h_id)->where('student_id', $s_id)->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function classes()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id');
    }

    public function class()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSemesterLabel::class, 'un_semester_label_id', 'id');
        }

        return $this->belongsTo(SmClass::class, 'class_id', 'id');

    }

    public function saasclass()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function sections()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id');
    }

    public function unSection()
    {
        return $this->belongsTo(UnSemesterLabelAssignSection::class, 'un_section_id', 'id');
    }

    public function saassection()
    {
        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function homeworkCompleted()
    {
        return $this->hasMany(SmHomeworkStudent::class, 'homework_id', 'id')->where('complete_status', 'C');
    }

    public function complete()
    {
        return $this->hasOne(\Modules\Lms\Entities\LessonComplete::class, 'lesson_id', 'id')->when(auth()->user()->role_id == 2, function ($q): void {
            $q->where('student_id', auth()->user()->student->id);
        });
    }

    public function lmsHomeworkCompleted()
    {
        return $this->hasOne(SmHomeworkStudent::class, 'homework_id', 'id');
    }

    public function subjects()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(UnSubject::class, 'un_subject_id', 'id');
        }

        return $this->belongsTo(SmSubject::class, 'subject_id', 'id');

    }

    public function saassubject()
    {
        return $this->belongsTo(SmSubject::class, 'subject_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }


    public function saasusers()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by', 'id');
    }

    public function getHomeworkPercentageAttribute()
    {
        try {
            $totalStudents = SmStudent::withOutGlobalScope(StatusAcademicSchoolScope::class)->select('id')
                ->where('class_id', $this->class_id)
                ->where('section_id', $this->section_id)
                ->where('school_id', auth()->user()->school_id)

                ->count();

            $totalHomeworkCompleted = SmHomeworkStudent::select('id')
                ->where('homework_id', $this->homework_id)
                ->where('academic_id', getAcademicId())
                ->where('complete_status', 'C')
                ->count();

            if (isset($totalStudents)) {
                return [
                    'totalStudents' => $totalStudents,
                    'totalHomeworkCompleted' => $totalHomeworkCompleted,

                ];
            }

            return false;

        } catch (Exception $exception) {
            return false;
        }
    }

    public function evaluations()
    {
        return $this->hasMany(SmHomeworkStudent::class, 'homework_id', 'id');
    }

    public function contents()
    {
        return $this->hasMany(SmUploadHomeworkContent::class, 'homework_id', 'id');
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

    public function semester()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo(UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function unSubject()
    {
        return $this->belongsTo(UnSubject::class, 'un_subject_id', 'id')->withDefault();
    }

    // public function records()
    // {
    //     return $this->hasManyThrough(StudentRecord::class, SmClass::class, 'id', 'class_id', 'id');
    // }

    public function course()
    {
        return $this->belongsTo(\Modules\Lms\Entities\Course::class, 'course_id', 'id')->withDefault();
    }

    public function lesson()
    {
        return $this->belongsTo(\Modules\Lms\Entities\CourseLesson::class, 'lesson_id', 'id')->withDefault();
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
