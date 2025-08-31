<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmEmailSmsLog extends Model
{
    use HasFactory;

    public static function saveEmailSmsLogData($request): void
    {

        $selectTabb = empty($request->selectTab) ? 'G' : $request->selectTab;
        $emailSmsData = new self();
        $emailSmsData->title = $request->email_sms_title;
        $emailSmsData->description = $request->description;
        $emailSmsData->send_through = $request->send_through;
        $emailSmsData->send_date = date('Y-m-d');
        $emailSmsData->send_to = $selectTabb;
        $emailSmsData->school_id = Auth::user()->school_id;
        $emailSmsData->academic_id = getAcademicId();
        $emailSmsData->save();
    }

    public static function un_saveEmailSmsLogData($request): void
    {
        $emailSmsData = new self();
        $emailSmsData->title = $request->email_sms_title;

        $common = App::make(UnCommonRepositoryInterface::class);
        $common->storeUniversityData($emailSmsData, $request);

        $emailSmsData->description = $request->description;
        $emailSmsData->send_through = $request->send_through;
        $emailSmsData->send_date = date('Y-m-d');
        $emailSmsData->send_to = $request->selectTab;
        $emailSmsData->school_id = Auth::user()->school_id;
        $emailSmsData->save();
    }

    public function sessionDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSession::class, 'un_session_id', 'id')->withDefault();
    }

    public function semesterDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemester::class, 'un_semester_id', 'id')->withDefault();
    }

    public function academicYearDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnAcademicYear::class, 'un_academic_id', 'id')->withDefault();
    }

    public function departmentDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnDepartment::class, 'un_department_id', 'id')->withDefault();
    }

    public function facultyDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnFaculty::class, 'un_faculty_id', 'id')->withDefault();
    }

    public function semesterLabelDetails()
    {
        return $this->belongsTo(\Modules\University\Entities\UnsemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function sectionDetails()
    {
        return $this->belongsTo(SmSection::class, 'un_semester_label_id', 'id')->withDefault();
    }
}
