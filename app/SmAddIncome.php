<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmAddIncome extends Model
{
    use HasFactory;

    protected $guerded = ['id'];

    protected $casts = ['date' => 'date'];

    public static function monthlyIncome(string $i)
    {
        try {
            if (moduleStatusCheck('University')) {
                return self::where('un_academic_id', getAcademicId())
                    ->where('name', '!=', 'Fund Transfer')
                    ->where('school_id', Auth::user()->school_id)
                    ->where('active_status', 1)
                    ->where('date', 'like', date('Y-m-').$i)
                    ->where('academic_id', getAcademicId())
                    ->sum('amount');
            }

            return self::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', 'like', date('Y-m-').$i)
                ->where('academic_id', getAcademicId())
                ->sum('amount');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function monthlyExpense(string $i)
    {
        try {
            if (moduleStatusCheck('University')) {
                return SmAddExpense::where('un_academic_id', getAcademicId())
                    ->where('name', '!=', 'Fund Transfer')
                    ->where('school_id', Auth::user()->school_id)
                    ->where('active_status', 1)
                    ->where('date', 'like', date('Y-m-').$i)
                    ->where('un_academic_id', getAcademicId())
                    ->sum('amount');
            }

            return SmAddExpense::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', 'like', date('Y-m-').$i)
                ->where('academic_id', getAcademicId())
                ->sum('amount');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function yearlyIncome(string $i)
    {
        try {
            if (moduleStatusCheck('University')) {
                return self::where('un_academic_id', getAcademicId())
                    ->where('name', '!=', 'Fund Transfer')
                    ->where('school_id', Auth::user()->school_id)
                    ->where('active_status', 1)
                    ->where('date', 'like', date('Y-'.$i).'%')
                    ->where('academic_id', getAcademicId())
                    ->sum('amount');
            }

            return self::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', 'like', date('Y-'.$i).'%')
                ->where('academic_id', getAcademicId())
                ->sum('amount');
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function yearlyExpense(string $i)
    {
        try {
            return SmAddExpense::where('academic_id', getAcademicId())
                ->where('name', '!=', 'Fund Transfer')
                ->where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->where('date', 'like', date('Y-'.$i).'%')
                ->where('academic_id', getAcademicId())
                ->sum('amount');
        } catch (Exception $exception) {
            return [];
        }
    }

    public function incomeHeads()
    {
        return $this->belongsTo(SmIncomeHead::class, 'income_head_id', 'id');
    }

    public function ACHead()
    {
        return $this->belongsTo(SmChartOfAccount::class, 'income_head_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(SmBankAccount::class, 'account_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method_id', 'id');
    }

    public function scopeAddIncome($query, $date_from, $date_to, $payment_method)
    {
        return $query->where('date', '>=', $date_from)
            ->where('date', '<=', $date_to)
            ->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->where('payment_method_id', $payment_method)
            ->where('academic_id', getAcademicId());
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);

    }
}
