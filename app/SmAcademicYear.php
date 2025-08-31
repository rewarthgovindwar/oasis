<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmAcademicYear extends Model
{
    use HasFactory;

    public static function API_ACADEMIC_YEAR($school_id)
    {
        try {
            $settings = SmGeneralSettings::where('school_id', $school_id)->first();
            if (moduleStatusCheck('University')) {
                return $settings->un_academic_id;
            }

            return $settings->session_id;
        } catch (Exception $exception) {
            return 1;
        }

    }

    public static function SINGLE_SCHOOL_API_ACADEMIC_YEAR()
    {
        try {
            $settings = SmGeneralSettings::where('school_id', 1)->first();
            if (moduleStatusCheck('University')) {
                return $settings->un_academic_id;
            }

            return $settings->session_id;

        } catch (Exception $exception) {
            return 1;
        }
    }

    public function scopeActive($query)
    {

        return $query->where('active_status', 1);
    }

    protected static function boot()
    {
        parent::boot();

        return static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
