<?php

namespace App;

use App\Models\DirectFeesInstallmentAssign;
use App\Models\DireFeesInstallmentChildPayment;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmFeesPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function discountMonth($discount, $month)
    {
        try {
            return self::where('active_status', 1)->where('fees_discount_id', $discount)->where('discount_month', $month)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function feesType()
    {
        return $this->belongsTo(SmFeesType::class, 'fees_type_id', 'id');
    }

    public function feesMaster()
    {
        return $this->belongsTo(SmFeesMaster::class, 'fees_type_id', 'fees_type_id');
    }

    public function recordDetail()
    {
        return $this->belongsTo(Models\StudentRecord::class, 'record_id', 'id');
    }

    public function feesInstallment()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(\Modules\University\Entities\UnFeesInstallmentAssign::class, 'un_fees_installment_id', 'id');
        }

        return $this->belongsTo(DirectFeesInstallmentAssign::class, 'direct_fees_installment_assign_id', 'id');

    }

    public function installmentPayment()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(\Modules\University\Entities\UnFeesInstallAssignChildPayment::class, 'installment_payment_id', 'id');
        }

        return $this->belongsTo(DireFeesInstallmentChildPayment::class, 'installment_payment_id', 'id');

    }
}
