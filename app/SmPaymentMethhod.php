<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmPaymentMethhod extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'method' => 'string',
    ];

    public function incomeAmounts()
    {
        return $this->hasMany(SmAddIncome::class, 'payment_method_id');
    }

    public function getIncomeAmountAttribute()
    {
        return $this->incomeAmounts->sum('amount');
    }

    public function expenseAmounts()
    {
        return $this->hasMany(SmAddExpense::class, 'payment_method_id');
    }

    public function getExpenseAmountAttribute()
    {
        return $this->expenseAmounts->sum('amount');
    }

    public function gatewayDetail()
    {
        return $this->hasOne(SmPaymentGatewaySetting::class, 'gateway_name', 'method')->where('school_id', auth()->user()->school_id);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
