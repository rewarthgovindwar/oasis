<?php

namespace App;

use App\Models\PayrollPayment;
use App\Scopes\AcademicSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmHrPayrollGenerate extends Model
{
    use HasFactory;

    public static function getPaymentMode($id)
    {

        try {
            $getPayrollDetails = SmPaymentMethhod::select('method')
                ->where('id', $id)
                ->first();
            if (isset($getPayrollDetails)) {
                return $getPayrollDetails->method;
            }

            return false;

        } catch (Exception $exception) {
            return false;
        }
    }

    public function staffs()
    {
        return $this->belongsTo(SmStaff::class, 'staff_id', 'id');
    }

    // public static function getPayrollDetails($staff_id, $payroll_month, $payroll_year){
    // 	try {
    // 		$getPayrollDetails = SmHrPayrollGenerate::select('id','payroll_status')
    // 							->where('staff_id', $staff_id)
    // 							->where('payroll_month', $payroll_month)
    // 							->where('payroll_year', $payroll_year)
    // 							->first();

    // 		if(isset($getPayrollDetails)){
    // 			return $getPayrollDetails;
    // 		}
    // 		else{
    // 			return false;
    // 		}
    // 	} catch (\Exception $e) {
    // 		return false;
    // 	}
    // }

    public function staffDetails()
    {
        return $this->belongsTo(SmStaff::class, 'staff_id', 'id');
    }

    public function paymentMethods()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_mode', 'id')->withDefault();
    }

    public function payrollPayments()
    {
        return $this->hasMany(PayrollPayment::class, 'sm_hr_payroll_generate_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
