<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmModule extends Model
{
    use HasFactory;

    public function moduleLink()
    {
        return $this->hasMany(SmModuleLink::class, 'module_id', 'id');
    }
}
