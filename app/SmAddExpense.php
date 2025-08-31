<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmAddExpense extends Model
{
    use HasFactory;

    protected $casts = ['date' => 'date'];

    public function expenseHead()
    {
        return $this->belongsTo(SmExpenseHead::class, 'expense_head_id', 'id');
    }

    public function ACHead()
    {
        return $this->belongsTo(SmChartOfAccount::class, 'expense_head_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(SmBankAccount::class, 'account_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method_id', 'id');
    }

    public function scopeAddExpense($query, $date_from, $date_to, $payment_method)
    {
        return $query->where('date', '>=', $date_from)
            ->where('date', '<=', $date_to)
            ->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->where('payment_method_id', $payment_method);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
