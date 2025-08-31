<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmUserLog extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(\Modules\RolePermission\Entities\InfixRole::class, 'role_id', 'id');
    }
}
