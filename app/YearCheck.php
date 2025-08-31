<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class YearCheck extends Model
{
    public static function getYear()
    {
        try {
            $year = generalSetting();
            if (moduleStatusCheck('University')) {
                return $year->unacademic_Year->created_at->format('Y');
            }

            return $year->academic_Year->year;

        } catch (Exception $exception) {
            return date('Y');
        }
    }

    public static function getAcademicId()
    {
        try {
            $year = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();

            return $year->session_id;
        } catch (Exception $exception) {
            return '1';
        }
    }

    public static function AcStartDate()
    {
        try {
            $start_date = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();

            return $start_date->academic_Year->starting_date;
        } catch (Exception $exception) {
            return date('Y');
        }
    }

    public static function AcEndDate()
    {
        try {
            $end_date = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();

            return $end_date->academic_Year->ending_date;
        } catch (Exception $exception) {
            return date('Y');
        }
    }
}
