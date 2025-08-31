<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{
    use HasFactory;

    protected $table = 'chat_group_users';

    protected $fillable = [
        'group_id', 'user_id', 'added_by', 'role', 'removed_by', 'deleted_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\GroupUserFactory::new();
    }
}
