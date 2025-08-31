<?php

namespace Modules\MenuManage\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternativeModule extends Model
{
    use HasFactory;

    protected $fillable = ['module_name', 'status'];

    protected static function newFactory()
    {
        return \Modules\MenuManage\Database\factories\AlternativeModuleFactory::new();
    }
}
