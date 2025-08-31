<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function activeValue()
    {
        return $this->hasOne(ColorTheme::class, 'color_id', 'id')->where('theme_id', color_theme()->id);
    }
}
