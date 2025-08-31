<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesAssign extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function discountSum($student_id, $type_id, $perpose, $record_id)
    {
        try {
            return SmFeesPayment::where('active_status', 1)
                ->where('student_id', $student_id)
                ->where('record_id', $record_id)
                ->where('fees_type_id', $type_id)
                ->sum($perpose);
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function groups($student_id)
    {

        return self::where('student_id', $student_id)
            ->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)
            ->get();
    }

    public static function createdBy($student_id, $discount_id, $record_id)
    {

        try {
            return SmFeesPayment::where('active_status', 1)
                ->where('student_id', $student_id)
                ->where('record_id', $record_id)
                ->where('fees_discount_id', $discount_id)
                ->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function feesPayment($type_id, $student_id, $record_id)
    {
        try {
            return SmFeesPayment::where('active_status', 1)
                ->where('fees_type_id', $type_id)
                ->where('student_id', $student_id)
                ->where(function ($query) use ($record_id): void {
                    $query->where('record_id', $record_id)
                        ->orWhereNull('record_id');
                })
                ->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function studentFeesTypeDiscount($group_id, $student_id, $discount_amount, $record_id)
    {
        try {
            return self::where('student_id', $student_id)
                ->where('record_id', $record_id)
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_masters.fees_group_id', '=', $group_id)
                ->where('sm_fees_assigns.applied_discount', '=', null)
                ->where('sm_fees_assigns.fees_amount', '>', 0)
                ->select('sm_fees_masters.id', 'sm_fees_types.id as fees_type_id', 'name', 'amount', 'sm_fees_assigns.student_id', 'applied_discount', 'sm_fees_masters.fees_group_id')
                ->get();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function feesGroupMaster()
    {
        return $this->belongsTo(SmFeesMaster::class, 'fees_master_id', 'id');
    }

    public function feesGroupMasterApi()
    {
        return $this->belongsTo(SmFeesMaster::class, 'fees_master_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function feesPayments()
    {
        return $this->hasMany(SmFeesPayment::class, 'assign_id');
    }

    public function getApplyDiscountSumAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('record_id', $this->record_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('applied_discount');
    }

    public function getDiscountSumAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('discount_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('amount');
    }

    public function getTotalFineAttribute()
    {
        return $this->feesPayments()->where('student_id', $this->student_id)->where('fees_type_id', $this->feesGroupMaster->feesTypes->id)->sum('fine');
    }

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function recordDetail()
    {
        return $this->belongsTo(Models\StudentRecord::class, 'record_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
