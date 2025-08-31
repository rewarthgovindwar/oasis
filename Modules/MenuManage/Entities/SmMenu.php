<?php

namespace Modules\MenuManage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\RolePermission\Entities\Permission;
class SmMenu extends Model
{
   
    protected $guarded = [];
    
    protected $table = 'sm_menus';

    public function permissionInfo()
    {
        return $this->belongsTo(Permission::class,'section_id');
    }

    public function sectionName()
    {
        return $this->belongsTo(self::class,'section_id');
    }

    public function childs()
    {
        return $this->hasMany(self::class,'parent_id');
    }

    public function parent(){
        return $this->hasOne(self::class,'parent_id');
    }

    public function deActiveChild()
    {
        return $this->hasMany(self::class,'parent_id')->where('menu_status',0);
    }
   
}
