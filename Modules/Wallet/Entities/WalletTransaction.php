<?php

namespace Modules\Wallet\Entities;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'user_id' => 'integer',
        'school_id' => 'integer',
        'amount' => 'float',
        'bank_id' => 'integer',
        'expense' => 'float',
        'academic_id' => 'integer',
    ];

    public function userName()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\Wallet\Database\factories\WalletTransactionFactory::new();
    }
}
