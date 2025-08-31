<?php

namespace Modules\Fees\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmFeesInvoiceChield extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'amount' => 'float',
        'due_amount' => 'float',
        'weaver' => 'float',
        'fine' => 'float',
    ];

    public function feesType()
    {
        return $this->belongsTo(FmFeesType::class, 'fees_type', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesInvoiceChieldFactory::new();
    }
}
