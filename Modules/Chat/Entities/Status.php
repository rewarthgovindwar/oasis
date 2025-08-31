<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'chat_statuses';

    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isInactive(): bool
    {
        return $this->status === 0;
    }

    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function isAway(): bool
    {
        return $this->status === 2;
    }

    public function isBusy(): bool
    {
        return $this->status === 3;
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\StatusFactory::new();
    }
}
