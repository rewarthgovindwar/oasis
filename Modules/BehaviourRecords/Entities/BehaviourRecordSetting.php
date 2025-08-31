<?php

namespace Modules\BehaviourRecords\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviourRecordSetting extends Model
{
    use HasFactory;

    protected $fillable = [];

    // protected static function newFactory()
    // {
    //     return \Modules\BehaviourRecords\Database\factories\BehaviourRecordSettingFactory::new();
    // }
}
