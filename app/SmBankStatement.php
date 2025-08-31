<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmBankStatement extends Model
{
    use HasFactory;

    public function bankName()
    {
        return $this->belongsTo(SmBankAccount::class, 'bank_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
