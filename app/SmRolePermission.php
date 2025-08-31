<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmRolePermission extends Model
{
    use HasFactory;

    public function moduleLink()
    {
        return $this->belongsTo(SmModuleLink::class, 'module_link_id', 'id');
    }
}
