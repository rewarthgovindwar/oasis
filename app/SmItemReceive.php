<?php

namespace App;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmItemReceive extends Model
{
    use HasFactory;

    public function suppliers()
    {
        return $this->belongsTo(SmSupplier::class, 'supplier_id', 'id');
    }

    public function paymentMethodName()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method', 'id');
    }

    public function bankName()
    {
        return $this->belongsTo(SmBankAccount::class, 'account_id', 'id');
    }

    public function itemPayments()
    {
        return $this->hasMany(SmInventoryPayment::class, 'item_receive_sell_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
