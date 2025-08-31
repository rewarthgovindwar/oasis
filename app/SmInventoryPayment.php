<?php

namespace App;

use App\Scopes\SchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmInventoryPayment extends Model
{
    use HasFactory;

    public static function itemPaymentdetails($item_receive_id)
    {

        try {
            $itemPaymentdetails = self::where('item_receive_sell_id', '=', $item_receive_id)->get();

            return count($itemPaymentdetails);
        } catch (Exception $exception) {
            return [];
        }
    }

    public function paymentMethods()
    {
        return $this->belongsTo(SmPaymentMethhod::class, 'payment_method', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SchoolScope);
    }
}
