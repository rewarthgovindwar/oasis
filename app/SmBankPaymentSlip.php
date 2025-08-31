<?php

namespace App;

use App\Models\DirectFeesInstallmentAssign;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmBankPaymentSlip extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function feesType()
    {
        return $this->belongsTo(SmFeesType::class, 'fees_type_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(SmBankAccount::class, 'bank_id', 'id');
    }

    public function feesInstallment()
    {
        return $this->belongsTo(\Modules\University\Entities\UnFeesInstallmentAssign::class, 'un_fees_installment_id', 'id');
    }

    public function installmentAssign()
    {
        return $this->belongsTo(DirectFeesInstallmentAssign::class, 'installment_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }
}
