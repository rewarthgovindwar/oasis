<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessageRemove extends Model
{
    use HasFactory;

    protected $table = 'chat_group_message_removes';

    protected $fillable = [
        'group_message_recipient_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\GroupMessageRemoveFactory::new();
    }
}
