<?php

namespace Modules\DownloadCenter\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentShareList extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'content_ids' => 'array',
        'gr_role_ids' => 'array',
        'ind_user_ids' => 'array',
        'section_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'shared_by', 'id');
    }
}
