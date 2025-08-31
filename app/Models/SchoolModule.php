<?php

namespace App\Models;

use App\SmSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolModule extends Model
{
    use HasFactory;

    protected $casts = [
        'modules' => 'array',
        'menus' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(SmSchool::class, 'school_id');
    }
}
