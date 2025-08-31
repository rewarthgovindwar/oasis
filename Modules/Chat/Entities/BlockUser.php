<?php

namespace Modules\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockUser extends Model
{
    use HasFactory;

    protected $table = 'chat_block_users';

    protected $fillable = [
        'block_by',
        'block_to',
    ];

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\BlockUserFactory::new();
    }
}
