<?php

namespace Modules\MenuManage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\RolePermission\Entities\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sidebar extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function permissionInfo()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id')->withDefault();
    }

    public function getPermission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');             
        
    }

    public function parentMenu()
    {
        return $this->belongsTo(Permission::class, 'parent', 'id')->withDefault();
    }

    public function deActiveChild()
    {
        return $this->hasMany(self::class, 'parent', 'permission_id')
            ->orderBy('position', 'ASC')
            ->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id)->where('active_status', 0);
    }

    public function userChildMenu()
    {
        return $this->hasMany(self::class, 'parent', 'permission_id')->when(is_role_based_sidebar(), function ($q): void {
            $q->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id);
        });
    }

    public function subModule()
    {
        $user = auth()->user();
        $is_role_based_sidebar = is_role_based_sidebar();

        return $this->hasMany(self::class, 'parent', 'permission_id')
            
            // ->whereNotIn('permission_id', deActivePermissions())
            ->with(['permissionInfo' => function ($q): void {
                $q->when(moduleStatusCheck('CustomMenu'), function ($q): void {
                    $q->with('customMenu');
                });
            }, 'subModule'])

            ->when(! $is_role_based_sidebar, function ($q) use ($user): void {
                $q->whereHas('permissionInfo', function ($q) use ($user): void {
                    $q->where('menu_status', 1)->when($user->role_id === 2, function ($q): void {
                        $q->where('is_student', 1);
                    })->when($user->role_id === 3, function ($q): void {
                        $q->where('is_parent', 1);
                    })->when($user->role_id === 4, function ($q): void {
                        $q->where('is_teacher', 1)->orWhere('is_admin', 1)->where('user_id', null);
                    })->when(! in_array($user->role_id, [2, 3, 4]), function ($q): void {
                        $q->where('is_admin', 1);
                    });
                })->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id);
            }, function ($q): void {
                $q->orWhere(function ($q): void {
                    $q->whereNull('user_id')->orWhereNull('role_id');
                });
            })
            ->where('active_status', 1)->orderBy('position', 'ASC');
    }


    public function subMenus()
    {        
       return $this->hasMany(self::class, 'parent', 'permission_id')->with(['permissionInfo', 'subMenus'])->orderBy('position', 'ASC');
    }

    public function deActiveSubMenu()
    {
        $user = auth()->user();

        return $this->hasMany(self::class, 'parent', 'permission_id')
            // ->whereNotIn('permission_id', deActivePermissions())
            ->with('permissionInfo', 'deActiveSubMenu')
            ->whereHas('permissionInfo', function ($q) use ($user): void {
                $q->where('menu_status', 1)->when($user->role_id === 2, function ($q): void {
                    $q->where('is_student', 1);
                })->when($user->role_id === 3, function ($q): void {
                    $q->where('is_parent', 1);
                })->when(! in_array($user->role_id, [2, 3]), function ($q): void {
                    $q->where('is_admin', 1);
                });
            })
            ->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id)->where('active_status', 0)->orderBy('position', 'ASC');
    }

    public function permissionSection()
    {
        return $this->belongsTo(PermissionSection::class, 'permission_id', 'id')->whereNotNull('parent_section')->withDefault();
    }

    public function scopeDeActiveMenuUser($q, $role_id = null)
    {
        return $q->where('ignore', 0)
            ->where('active_status', 0)
            ->when(! $role_id, function ($q): void {
                $q->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id);
            }, function ($q) use ($role_id): void {
                $q->where('role_id', $role_id)->whereNull('user_id');
            });
    }

    public function scopeActiveMenuUser($q, $role_id = null)
    {
        return $q->where('ignore', 0)
            ->where('active_status', 1)
            ->when(! $role_id, function ($q): void {
                $q->where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id);
            }, function ($q) use ($role_id): void {
                $q->where('role_id', $role_id);
            });
    }
}
