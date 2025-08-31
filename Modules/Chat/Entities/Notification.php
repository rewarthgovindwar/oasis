<?php

namespace Modules\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\NotificationFactory::new();
    }
}
