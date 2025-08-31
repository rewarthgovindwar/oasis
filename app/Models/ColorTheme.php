<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorTheme extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'color_theme';
}
