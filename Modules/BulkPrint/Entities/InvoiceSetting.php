<?php

namespace Modules\BulkPrint\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\BulkPrint\Database\factories\InvoiceSettingFactory::new();
    }
}
