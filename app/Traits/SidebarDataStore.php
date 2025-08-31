<?php

namespace App\Traits;

use App\GlobalVariable;
use App\InfixModuleManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\Saas\Entities\SaasSettings;
use Modules\MenuManage\Entities\Sidebar;
use Modules\RolePermission\Entities\Permission;

trait SidebarDataStore
{


    function defaultSidebarStore($role_id = null)
    {


        $is_role_based_sidebar = is_role_based_sidebar();

        $user = auth()->user();
        if (!$role_id) {
            $role_id = $user->role_id;
        }
        $cache_key = sidebar_cache_key($role_id);

        $exit = Sidebar::when(!$is_role_based_sidebar, function ($q) use ($user) {
            $q->where('user_id', auth()->user()->id)->where('role_id', $user->role_id);
        }, function ($q) use ($role_id) {
            $q->where('role_id', $role_id)->whereNull('user_id');
        })->first();


        if ($exit) {
            return true;
        }


        $permissionInfos = $this->permissions($role_id);


        /*if ($role_id == 2 || $role_id == 3) {

            Sidebar::updateOrCreate([
                'permission_id' => 1,
                'user_id' => $is_role_based_sidebar ? null : $user->id,
                'role_id' => $role_id,

            ], [
                'position' => 1,
                'level' => 1,
                'parent' => null,
            ]);


        }*/
        if ($role_id == 2 || $role_id == 3) {
            foreach ($permissionInfos as $key => $sidebar) {
                $parent_id = $this->parentId($sidebar, $role_id);
                $this->storeSidebar($sidebar, $key, $parent_id, $role_id);
            }

            Cache::forget($cache_key);


        } else {
            $this->resetSidebarStore($role_id);
        }

        if ($role_id == 2 || $role_id == 3) {
            Sidebar::whereNull('parent')->when(!$is_role_based_sidebar, function ($q) {
                $q->where('user_id', auth()->user()->id);
            }, function ($q) use ($role_id) {
                $q->where('role_id', $role_id);
            })->where('permission_id', '!=', 1)->update(['parent' => 1]);
        }

        Cache::forget($cache_key);

    }

    function resetSidebarStore($role_name = 'staff')
    {
        
        $is_role_based_sidebar = is_role_based_sidebar();
        $role_ids = $this->getRoleids($role_name);
        $user = auth()->user();
        

        $dashboardSections = ["dashboard", "menumanage.index"];
        $administration_sections = ["admin_section", "academics", "study_material", 'download-center', "lesson-plan", "bulk_print", "certificate", "university", "lms"];
        $student_sections = ["student_info", "fees", "fees_collection", "transport", "dormitory", "library", "homework", "behaviour_records", "alumni_records", "qr_code_attendance"];
        $alumni_sections = ["student_info", "fees", "fees_collection", "transport", "dormitory", "library", "homework", "behaviour_records", "alumni_records", "qr_code_attendance"];
        $exam_sections = ["examination", "online_exam", "examplan"];
        $hr_sections = ["role_permission", "human_resource", "teacher-evaluation", "leave"];
        $account_sections = ["accounts", "inventory", "wallet"];
        $utilities_sections = ["chat", "style", "communicate"];
        $report_sections = ["students_report", "exam_report", "staff_report", "fees_report", "accounts_report"];
        $settings_sections = ["general_settings", "fees_settings", "exam_settings", "frontend_cms", "custom_field"];

        //permission section
        $permissionSections = include './resources/var/permission/permission_section_sidebar.php';
        
        $permissionSectionRoutes = [];
        foreach ($permissionSections as $item) {
            if($role_name == 'student'){
                $role_id = 2;
            }elseif($role_name == 'parent'){
                $role_id = 3;
            }else{
                $role_id = 1;
            }
            storePermissionData($item, null, null, $role_id);
        }

        // ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
        //     ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
        //     ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
        // end
        $userPermissionSections = Permission::with('parent:id,permission_section')
            ->where('permission_section', 1)
            ->whereNotNull('route')
            ->where('is_saas', 0);
        if($role_name == 'student'){
            $userPermissionSections = $userPermissionSections->where('is_student',1);
        }

        if($role_name == 'parent'){
            $userPermissionSections = $userPermissionSections->where('is_parent',1);
        }

        if($role_name == 'staff'){
            $userPermissionSections = $userPermissionSections->where('is_admin',1);
        }

        $userPermissionSections = $userPermissionSections->get(['id', 'name', 'type', 'route', 'parent_route', 'permission_section']);
       
        
        foreach ($userPermissionSections as $key => $userSection) {
            $parent = $userSection->parent?->id;
            $this->storeSidebar($userSection, $key, $parent, $role_id);
        }
        $permissionInfos = $this->permissions($role_name);
        foreach ($permissionInfos as $key => $sidebar) {
            
            $parent_id = $this->parentId($sidebar, $role_name);
            if (in_array($sidebar->route, $dashboardSections)) {
               
                $p_id = Permission::where('route', 'dashboard_section')
                    ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                    ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                    ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)
                    ->orWhere('is_teacher',1); })
                    ->value('id');
            }
            if (in_array($sidebar->route, $administration_sections)) {
                $parent_id = Permission::where('route', 'administration_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }
            if (in_array($sidebar->route, $student_sections)) {
                $parent_id = Permission::where('route', 'student_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }

            if (in_array($sidebar->route, $exam_sections)) {
                $parent_id = Permission::where('route', 'exam_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }
            if (in_array($sidebar->route, $hr_sections)) {
                $parent_id = Permission::where('route', 'hr_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }
            if (in_array($sidebar->route, $account_sections)) {
                $parent_id = Permission::where('route', 'accounts_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }
            if (in_array($sidebar->route, $utilities_sections)) {
                $parent_id = Permission::where('route', 'utilities_section')
                    ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                    ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                    ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                    ->value('id');
            }
            if (in_array($sidebar->route, $report_sections)) {
                $parent_id = Permission::where('route', 'report_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }
            if (in_array($sidebar->route, $settings_sections)) {
                $parent_id = Permission::where('route', 'settings_section')
                ->when($role_name == 'student',function($q){ $q->where('is_student',1); })
                ->when($role_name == 'parent',function($q){ $q->where('is_parent',1); })
                ->when($role_name == 'staff',function($q){ $q->where('is_admin',1)->orWhere('is_teacher',1); })
                ->value('id');
            }

            if (!$sidebar->route && !$sidebar->parent_route) {
                continue;
            }

           
            $this->storeSidebar($sidebar, $key, $parent_id, $role_id);
        }

        
        $ignorePermissionRoutes = ['reports', 'fees.fees-report', 'exam-setting'];
        $getIgnoreIds = Permission::whereIn('route', $ignorePermissionRoutes)->pluck('id')->toArray();
        Cache::forget(sidebar_cache_key($role_id));
        Sidebar::whereIn('permission_id', $getIgnoreIds)->update(['active_status' => 0, 'ignore' => 1]);
        $this->deActiveForSaas();

        # Delete exra route menu from permission table
        $fees_setting = Permission::where('route', 'fees_settings')->where('parent_route', 'settings_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $exam_settings = Permission::where('route', 'exam_settings')->where('parent_route', 'settings_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $student_report = Permission::where('route', 'students_report')->where('parent_route', 'report_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $exam_report = Permission::where('route', 'exam_report')->where('parent_route', 'report_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $staff_report = Permission::where('route', 'staff_report')->where('parent_route', 'report_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $fees_report = Permission::where('route', 'fees_report')->where('parent_route', 'report_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
        $accounts_report = Permission::where('route', 'accounts_report')->where('parent_route', 'report_section')->where('type', null)->whereNull('user_id')->where('role_id', 1)->where('school_id', 1)->delete();
    }

    function permissions($role_name = 'staff')
    {

        $perInfo = Permission::where('is_menu', 1)->with('parent:id,permission_section');
        if($role_name == 'student'){
            $perInfo = $perInfo->where('is_student', 1);
        }
        
        if($role_name == 'parent'){
           $perInfo =  $perInfo->where('is_parent', 1);
        }
        
        if($role_name == 'staff'){
           $perInfo = $perInfo->where('is_admin', 1)->orWhere('is_teacher',1);
        }
        $permissionInfos = $perInfo->orderBy('position', 'ASC')->get(['id', 'name', 'type', 'route', 'parent_route', 'position', 'permission_section']);
       
        // $is_role_based_sidebar = is_role_based_sidebar();

        // if (!$is_role_based_sidebar) {
        //     $user = auth()->user();
        //     if ($user->role_id == 1) {
        //         $permissionInfos = Permission::where('is_admin', 1)->where('is_menu', 1)
        //             ->with('parent:id,permission_section')
        //             ->where('is_saas', 0)
        //             ->where(function ($q) {
        //                 $q->whereNull('role_id')->where(function ($q) {
        //                     $q->where('user_id', auth()->user()->id)->orWhereNull('user_id');
        //                 });
        //             })
        //             ->get(['id', 'name', 'type', 'route', 'parent_route', 'permission_section']);
        //     } else {
        //         $permissionInfos = Permission::where('is_menu', 1)
        //             ->with('parent:id,permission_section')
        //             ->orderBy('position', 'ASC')
        //             ->when(!in_array($user->role_id, [2, 3, GlobalVariable::isAlumni()]), function ($q) {
        //                 $q->where('is_admin', 1);
        //             })->when($user->role_id == 4, function ($q) {
        //                 $q->orWhere('is_teacher', 1);
        //             })->when($user->role_id == 2, function ($q) {
        //                 $q->where('is_student', 1);
        //             })->when($user->role_id == 3, function ($q) {
        //                 $q->where('is_parent', 1);
        //             })->where(function ($q) {
        //                 $q->whereNull('role_id')->where('user_id', auth()->user()->id)->orWhereNull('user_id');
        //             })->when($user->role_id == GlobalVariable::isAlumni(), function ($q) {
        //                 $q->where('is_alumni', 1);
        //             })
        //             ->get(['id', 'name', 'type', 'route', 'parent_route', 'position', 'permission_section']);
        //     }
        //     return $permissionInfos;
        // }

        // if ($role_id == 1) {
        //     $permissionInfos = Permission::where('is_admin', 1)->where('is_menu', 1)
        //         ->with('parent:id,permission_section')
        //         ->where('is_saas', 0)
        //         ->where(function ($q) use ($role_id) {
        //             $q->where('role_id', $role_id)->orWhere(function ($q) {
        //                 $q->whereNull('role_id')->whereNull('user_id');
        //             });
        //         })
        //         ->get(['id', 'name', 'type', 'route', 'parent_route', 'permission_section']);
        // } else {
        //     $permissionInfos = Permission::where('is_menu', 1)
        //         ->with('parent:id,permission_section')
        //         ->orderBy('position', 'asc')
        //         ->when(!in_array($role_id, [2, 3, GlobalVariable::isAlumni()]), function ($q) {
        //             $q->where('is_admin', 1);
        //         })->when($role_id == 4, function ($q) {
        //             $q->where(function ($q) {
        //                 $q->where('is_admin', 1)->orWhere('is_teacher', 1);
        //             });
        //         })->when($role_id == 2, function ($q) {
        //             $q->where('is_student', 1);
        //         })->when($role_id == 3, function ($q) {
        //             $q->where('is_parent', 1);
        //         })->where(function ($q) use ($role_id) {
        //             $q->where(function ($q) use ($role_id) {
        //                 $q->where('role_id', $role_id);
        //             })->orWhere(function ($q) {
        //                 $q->whereNull('role_id')->whereNull('user_id');
        //             });
        //         })->when($role_id == GlobalVariable::isAlumni(), function ($q) {
        //             $q->where('is_alumni', 1);
        //         })
        //         ->get(['id', 'name', 'type', 'route', 'parent_route', 'position', 'permission_section']);
        // }
        return $permissionInfos;

    }

    function storeSidebar($sidebar, $key, $parent_id, $role_id)
    {
        
        $is_role_based_sidebar = is_role_based_sidebar();

        $user = auth()->user();

        Sidebar::updateOrCreate([
            'permission_id' => $sidebar->id,
            'role_id' => $role_id

        ], [
            'position' => $key + 1,
            'level' => $sidebar->type,
            'parent' => $parent_id,
        ]);
       
    }

    function modulePermissionSidebar($role_id = null)
    {

        $is_role_based_sidebar = is_role_based_sidebar();
        $user = auth()->user();

        if (!$role_id) {
            $role_id = $user->role_id;
        }

        $permissionIds = $this->permissions($role_id)->whereNotNull('route')->pluck('id')->toArray();
        
        $sidebarPermissionIds = Sidebar::when(!$is_role_based_sidebar, function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('role_id', $user->role_id);
        }, function ($q) use ($role_id) {
            $q->where('role_id', $role_id)->orWhere(function ($q) {
                $q->whereNull('role_id')->whereNull('user_id');
            });
        })->pluck('permission_id')->toArray();

        $newPermissionIds = array_diff($permissionIds, $sidebarPermissionIds);

        if (empty($newPermissionIds)) return true;

        if (count($newPermissionIds) > 0) {
            $permissionInfos = Permission::whereIn('id', $newPermissionIds)->get(['id', 'name', 'type', 'route', 'parent_route', 'position', 'permission_section']);

            foreach ($permissionInfos as $key => $sidebar) {
                $parent_id = $this->parentId($sidebar, $role_id);

                if (!$sidebar->route && !$sidebar->parent_route) {
                    continue;
                }
                $this->storeSidebar($sidebar, $key, $parent_id, $role_id);
            }
            if ($role_id == 2 || $role_id == 3) {
                Sidebar::whereNull('parent')->when(!$is_role_based_sidebar, function ($q) {
                    $q->where('user_id', auth()->user()->id);
                }, function ($q) use ($role_id) {
                    $q->where('role_id', $role_id);
                })->where('permission_id', '!=', 1)->update(['parent' => 1]);
            }
            Cache::forget(sidebar_cache_key($role_id));
        }

    }

    function parentId($sidebar, $role_name = 'staff')
    {
        
        $is_role_based_sidebar = is_role_based_sidebar();
        // if (!$role_id) {
        //     $role_id = auth()->user()->role_id;
        // }

        if (in_array($sidebar->route, $this->paidModuleRoutes())) {
            return Permission::where('route', 'module_section')
                ->when($role_name == 'student',function($q){
                    $q->where('is_student',1);
                })->when($role_name == 'parent',function($q){
                    $q->where('is_parent',1);
                })->when($role_name == 'staff',function($q){
                    $q->where('is_admin',1)->orWhere('is_teacher',1);
                })->value('id');
        }

        $parent = $sidebar->parent;
        
        if (!empty($parent) && $parent->permission_section == 1 && $sidebar->permission_section) {
            $parent_id = null;
        } elseif (!empty($parent) && $parent->permission_section == 1 && !$sidebar->permission_section) {
            $parent_id = $parent->id;
        } elseif ($parent) {
            $parent_id = $parent->id;
        } else {
            $parent_id = 1;
        }

        if ($sidebar->permission_section == 1) {
            $parent_id = null;
        }
        return $parent_id;
    }

    function allActivePaidModules()
    {
        $activeModules = [];
        $modules = Cache::rememberForever('paid_modules', function () {
            return InfixModuleManager::where('is_default', false)->where('name', '!=', 'OnlineExam')->pluck('name')->toArray();
        });
        foreach ($modules as $module) {
            if (moduleStatusCheck($module)) {
                $activeModules [] = $module;
            }
        }
        return $activeModules;
    }

    function paidModuleRoutes($role_name = 'staff')
    {
        // ->when($role_name == 'student',function($q){
        //         $q->where('is_student',1);
        //     })->when($role_name == 'parent',function($q){
        //         $q->where('is_parent',1);
        //     })->when($role_name == 'staff',function($q){
        //         $q->where('is_admin',1)->orWhere('is_teacher',1);
        //     })
        $permission =  Permission::whereIn('module', $this->allActivePaidModules())->with('parent:id,permission_section')->whereNotNull('route')->whereNull('parent_route')->whereNotNull('module');
            if($role_name == 'student'){
                $permission = $permission->where('is_student', 1);
            }
            
            if($role_name == 'parent'){
            $permission =  $permission->where('is_parent', 1);
            }
            
            if($role_name == 'staff'){
                $permission = $permission->where('is_admin', 1)->orWhere('is_teacher',1);
            }
        return $permission = $permission->pluck('route')->toArray();
    }

    function deActiveForPgsql()
    {
        if (db_engine() != 'mysql') {
            Permission::whereIn('route', ['backup-settings'])->update(['is_menu' => 0, 'menu_status' => 0, 'status' => 0]);
        }
    }

    function deActiveForSaas()
    {
        if (moduleStatusCheck('Saas')) {
            $list = ['update-system', 'utility', 'manage-adons', 'backup-settings', 'utility', 'language-list'];
            Permission::whereIn('route', $list)->update(['is_menu' => 0, 'menu_status' => 0, 'status' => 0, 'is_saas' => 1]);
            $saasSettingsRoutes = SaasSettings::where('saas_status', 1)->pluck('route')->toArray();
            if ($saasSettingsRoutes) {
                Permission::whereIn('route', $saasSettingsRoutes)->update(['is_menu' => 1, 'menu_status' => 1, 'status' => 1, 'is_saas' => 0]);
            }
        }

    }
}
