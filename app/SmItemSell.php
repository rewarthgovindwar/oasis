<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmItemSell extends Model
{
    use HasFactory;

    public function roles()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'role_id', 'id');
    }

    public function staffDetails()
    {
        return $this->belongsTo(SmStaff::class, 'student_staff_id', 'id');
    }

    public function parentsDetails()
    {
        return $this->belongsTo(SmParent::class, 'student_staff_id', 'id');
    }

    public function studentDetails()
    {
        return $this->belongsTo(SmStudent::class, 'student_staff_id', 'id');
    }

    public function paymentMethodName()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method', 'id');
    }

    public function bankName()
    {
        return $this->belongsTo(SmBankAccount::class, 'account_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
