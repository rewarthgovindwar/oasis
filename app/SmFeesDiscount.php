<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesDiscount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function CheckAppliedDiscount($discount_id, $student_id, $record_id): ?string
    {
        $check = SmFeesAssign::where('fees_discount_id', $discount_id)->where('record_id', $record_id)->where('student_id', $student_id)->first();
        if ($check) {
            // code...
            $assigned_fees_amount = $check->fees_amount + $check->applied_discount;
            $main_fees_amount = SmFeesMaster::find($check->fees_master_id);
            if ((float) ($main_fees_amount->amount) < (float) $assigned_fees_amount) {
                return 'true';
            }

            if ($main_fees_amount->amount > $assigned_fees_amount) {
                return 'false';
            }

            return 'true';

        }

        return null;

    }

    public static function CheckAppliedYearlyDiscount($discount_id, $student_id): string
    {
        $check = SmFeesAssignDiscount::where('fees_discount_id', $discount_id)->where('student_id', $student_id)->first();
        if ($check) {
            return 'false';
        }

        return 'true';

    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
