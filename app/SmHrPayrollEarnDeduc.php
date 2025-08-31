<?php

namespace App;

use App\Scopes\AcademicSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmHrPayrollEarnDeduc extends Model
{
    use HasFactory;

    public static function getTotalEarnings($payroll_generate_id)
    {

        try {
            $totalEarnings = self::where('payroll_generate_id', $payroll_generate_id)
                ->where('earn_dedc_type', 'E')
                ->sum('amount');

            return $totalEarnings ?? false;

        } catch (Exception $exception) {
            return false;
        }
    }

    public static function getTotalDeductions($payroll_generate_id)
    {

        try {
            $totalDeductions = self::where('payroll_generate_id', $payroll_generate_id)
                ->where('earn_dedc_type', 'D')
                ->sum('amount');

            return $totalDeductions ?? false;

        } catch (Exception $exception) {
            return false;
        }
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
