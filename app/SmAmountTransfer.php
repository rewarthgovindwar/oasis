<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmAmountTransfer extends Model
{
    use HasFactory;

    public function fromPaymentMethodName()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'from_payment_method', 'id');
    }

    public function toPaymentMethodName()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'to_payment_method', 'id');
    }
}
