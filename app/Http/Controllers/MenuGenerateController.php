<?php

namespace App\Http\Controllers;

use App\InfixModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class MenuGenerateController extends Controller
{
    private $file = [
        'admin' => 'staff',
        'student' => 'student',
        'parent' => 'parent',
    ];

    public function index(Request $request, $for = null)
    {
        $roles = [
            'admin', 'student', 'parent'
        ];
        if (!$for) {
            foreach ($roles as $role) {
//                $this->menuGenerate($role);
                $this->moduleMenuGenerate($role);
            }
            dd('done for all menu generate');
        }

        if (!in_array($for, $roles)) {
            abort(404);
        }


//        $this->menuGenerate($for);
        $this->moduleMenuGenerate($for);

        dd('done for ' . $for . ' menu generate');


    }

    private function menuGenerate($for)
    {
        $func = $for . 'MenuGenerate';
        if (!method_exists($this, $func)) {
            abort(403);
        }

        $this->$func();

        dump('menu generate for ' . $for . ' done');
    }

    private function moduleMenuGenerate($for)
    {

        $modules = InfixModuleManager::query()->where('is_default', false)->where('name', 'University')->pluck('name')->toArray();
        foreach ($modules as $module) {
            if (moduleStatusCheck($module)) {
                $html = '';

                $permissions = $this->permissions(function ($query) use ($module, $for) {
                    $query->where('module', $module)->whereNull('parent_route');
                    $permissionQuery = $for . 'PermissionQuery';
                    return $this->$permissionQuery($query);

                });
                if ($permissions->count() < 1) {
                    continue;
                }


                    $html .= "\n" . $this->menuHtml($permissions, $for, true);


                File::ensureDirectoryExists(module_path($module) . '/Resources/views/menu');
                $file = $this->file[$for];
                file_put_contents(module_path($module) . '/Resources/views/menu/' . $file . '.blade.php', $html);
            }
        }

        dump('module menu generate for ' . $for . ' done');
    }

    private function adminPermissionQuery($query)
    {
        return $query->with(['subModule' => function ($q) {
            $q->where(function ($q) {
                $q->where('is_admin', 1)->orWhere('is_teacher', 1);
            });
        }])->where('is_admin', 1);
    }

    private function studentPermissionQuery($query)
    {
        return $query->with(['subModule' => function ($q) {
            $q->where(function ($q) {
                $q->where('is_student', 1);
            });
        }])
            ->where('is_student', 1);
    }

    private function parentPermissionQuery($query)
    {
        return $query->with(['subModule' => function ($q) {
            $q->where(function ($q) {
                $q->where('is_parent', 1);
            });
        }])->where('is_parent', 1);
    }

    private function studentMenuGenerate()
    {

        $html = '';
        $permissions = $this->permissions(function ($query) {
            $query = $this->studentPermissionQuery($query);
            return $query->where(function ($q) {
                $q->whereNull('module')->orWhereIn('module', ['Fees', 'fees_collection', 'DownloadCenter']);
            })
                ->whereNull('parent_route')
                ->whereNotNull('route');
        });
        $html .= "\n" . $this->menuHtml($permissions, 'student');

        $html .= "\n" . ' <span class="menu_seperator" id="seperator_module_section" data-section="module"> {{ __("common.module")}} </span>';

        $html .= "\n" . ' @foreach($paid_modules as $module)';
        $html .= "\n" . ' @includeIf(strtolower($module)."::menu.student")';
        $html .= "\n" . " @endforeach";

        file_put_contents(resource_path('views/backEnd/menu/student.blade.php'), $html);

    }


    private function parentMenuGenerate()
    {

        $html = '';
        $permissions = $this->permissions(function ($query) {
            $query = $this->parentPermissionQuery($query);
            return $query->where(function ($q) {
                $q->whereNull('module')->orWhereIn('module', ['Fees', 'fees_collection', 'DownloadCenter']);
            })
                ->whereNull('parent_route')
                ->whereNotNull('route');
        });

        $html .= "\n" . $this->menuHtml($permissions, 'parent');

        $html .= "\n" . ' <span class="menu_seperator" id="seperator_module_section" data-section="module"> {{ __("common.module")}} </span>';

        $html .= "\n" . ' @foreach($paid_modules as $module)';
        $html .= "\n" . ' @includeIf(strtolower($module)."::menu.parent")';
        $html .= "\n" . " @endforeach";

        file_put_contents(resource_path('views/backEnd/menu/parent.blade.php'), $html);
    }

    private function adminMenuGenerate()
    {
        $all_sections = [
            'common.dashboard' => ["dashboard", "menumanage.index"],
            'common.Administration' => ["admin_section", "academics", "study_material", 'download-center', "lesson-plan", "bulk_print", "certificate"],
            'common.student' => ["student_info", "fees", "fees_collection", "transport", "dormitory", "library", "homework", "behaviour_records", "alumni_records"],
            'common.exam' => ["examination", "online_exam", "examplan"],
            'common.hr' => ["role_permission", "human_resource", "teacher-evaluation", "leave"],
            'common.accounts' => ["accounts", "inventory", "wallet"],
            'common.Utilities' => ["chat", "style", "communicate"],
            'common.report_section' => ["students_report", "exam_report", "staff_report", "fees_report", "accounts_report"],
            'common.settings_section' => ["general_settings", "fees_settings", "exam_settings", "frontend_cms", "custom_field"],
//        'common.module' => $activeModules,
            //common.module
        ];
        $html = '';

        foreach ($all_sections as $key => $sections) {
            $section = strtolower(str_replace('common.', '', $key));
            $html .= "\n" . ' <span class="menu_seperator" id="seperator_' . ($section . '_section') . '" data-section="' . $section . '"> {{ __("' . $key . '")}} </span>';

            $permissions = $this->permissions(function ($query) use ($sections) {
                $query = $this->adminPermissionQuery($query)
                ;
                return $query->whereIn('route', $sections)->where(function ($q) {
//                        $q->whereNull('module')->orWhereIn('module', ['Fees', 'fees_collection', 'DownloadCenter']);
            });
            });
            $html .= "\n" . $this->menuHtml($permissions, 'admin');

        }


        $html .= "\n" . ' <span class="menu_seperator" id="seperator_module_section" data-section="module"> {{ __("common.module")}} </span>';

        $html .= "\n" . ' @foreach($paid_modules as $module)';
        $html .= "\n" . ' @includeIf(strtolower($module)."::menu.staff")';
        $html .= "\n" . " @endforeach";

//    $html .= "\n".' </ul>';


        file_put_contents(resource_path('views/backEnd/menu/staff.blade.php'), $html);
    }

    private function permissions($func)
    {
        $query = \Modules\RolePermission\Entities\Permission::query()
            ->where('route', '!=', 'menumanage.index')
            ->where('menu_status', 1)
            ->where('is_menu', 1)->orderBy('position', 'asc');

        $query = $func($query);

        return $query->get();
    }

    private function menuHtml($permissions, $for, $module = false)
    {
        $html = '';
        $saasExcludedRoute = [
            'general-settings', 'backup-settings', 'cron-job', 'manage-adons'
        ];
        $saasIncludedRoute = [
            'school-general-settings', 'administrator-notice'
        ];

        $universityExcludedRoute = [
            'academic-year', 'class_optional'
        ];

        $replaceIsMenu = [
            'lesson-plan' => 'lesson_plan',
            'download-center' => 'download_center',
            'frontend_cms' => 'front_setting',
            'general_settings' => 'system_settings',
            'fees_collection' => 'fees'
        ];
        $dashboardRoute = [
            'student-dashboard', 'parent-dashboard', 'dashboard'
        ];
        foreach ($permissions as $permission) {
            $submodules = $permission->subModule->pluck('route')->prepend($permission->route);
            $active = '';
            foreach ($submodules as $submodule) {
                $active .= "'" . $submodule . "',";
            }
            $alternate_module = $permission->alternate_module;
            if($alternate_module){
                $html .= "\n".'@if (!moduleStatusCheck(\''.$permission->alternate_module.'\'))';
            }
            if($permission->module == 'Fees') {
                $html .= "\n".'@if ((bool)generalSetting()->fees_status)';
            }

            if($permission->module == 'fees_collection') {
                $html .= "\n".'@if (!(bool)generalSetting()->fees_status)';
            }
            $isMenu = $replaceIsMenu[$permission->route] ?? $permission->route;

            $html .= "\n" . ' @if(';

            if(!$module && !in_array($permission->route, $dashboardRoute)) {
                $html .=  'isMenuAllowToShow("' . $isMenu . '") && ';
            }

            $html .= ' userPermission("' . $permission->route . '"))';
            $html .= "\n" . ' <li class="{{ spn_active_link([' . $active . '], "mm-active") }} ' . $permission->route . ' main">';

            if (($permission->subModule->count() > 0 && $permission->route != 'dashboard') || $permission->relate_to_child) {
                $html .= "\n" . ' <a href="javascript:void(0)" class="has-arrow" aria-expanded="false">';
            } else {
                $html .= "\n" . ' <a href="{{ validRouteUrl(\'' . $permission->route . '\') }}">';
            }

            $html .= "\n" . ' <div class="nav_icon_small">';
            $html .= "\n" . ' <span class="' . $permission->icon . '"></span>';
            $html .= "\n" . ' </div>';
            $html .= "\n" . ' <div class="nav_title">';
            $html .= "\n" . ' <span>{{ __(\'' . ($permission->lang_name ?? $permission->name) . '\') }}</span>';

            $html .= "\n" . ' </div> ' . "\n" . ' </a>';

            if ($permission->subModule->count() > 0 || $permission->relate_to_child) {
                $html .= "\n" . ' <ul class="mm-collapse">';

                if ($permission->subModule->count() > 0) {
                    foreach ($permission->subModule as $submodule) {
                        $excludeFeesRouteList = ['fees_group', 'fees_type', 'search_fees_due', 'fees_forward'];
                        if(in_array($submodule->route, $excludeFeesRouteList)) {
                            $html .= "\n" . ' @if(!directFees())';
                        }
                        $html .= "\n" . ' @if(userPermission("' . $submodule->route . '")';
                        if(in_array($submodule->route, $saasExcludedRoute)) {
                            $html .= ' && !moduleStatusCheck("Saas")';
                        }
                        if(in_array($submodule->route, $saasIncludedRoute)) {
                            $html .= ' && moduleStatusCheck("Saas")';
                        }
                        if(in_array($submodule->route, $universityExcludedRoute)) {
                            $html .= ' && !moduleStatusCheck("University")';
                        }

                        if($submodule->route == 'saas.custom-domain') {
                            $html .= ' && config("app.allow_custom_domain")';
                        }
                        $html .= ')';
                        if ($submodule->relate_to_child && $for== 'parent') {
                            $html .= "\n" . ' @foreach($children as $child)';
                            $html .= "\n" . ' <li class="sub">';
                            $html .= "\n" . ' <a href="{{ validRouteUrl(\'' . $submodule->route . '\', $child->id) }}" class="{{ spn_active_link(\'' . $submodule->route . '\') }}">';
                            $html .= "\n" . '{{ __(\'' . ($submodule->lang_name ?? $submodule->name) . '\') }} - {{ $child->full_name }}';
                            $html .= "\n" . ' </a>';
                            $html .= "\n" . ' </li>';
                            $html .= "\n" . "\n @endforeach";

                        } else {
                            $html .= "\n" . ' <li class="sub">';
                            $html .= "\n" . ' <a href="{{ validRouteUrl(\'' . $submodule->route . '\') }}" class="{{ spn_active_link(\'' . $submodule->route . '\') }}">';
                            $html .= "\n" . '{{ __(\'' . ($submodule->lang_name ?? $submodule->name) . '\') }}';
                            $html .= "\n" . ' </a>';
                            $html .= "\n" . ' </li>';
                        }
                        $html .= "\n" . ' @endif';
                        if(in_array($submodule->route, $excludeFeesRouteList)) {
                            $html .= "\n" . ' @endif';
                        }

                    }

                } else {
                    $excludeFeesRouteList = ['fees_group', 'fees_type', 'search_fees_due', 'fees_forward'];
                    if(in_array($permission->route, $excludeFeesRouteList)) {
                        $html .= "\n" . ' @if(!directFees())';
                    }
                    $html .= "\n" . ' @if(userPermission("' . $permission->route . '"))';
                    if ($permission->relate_to_child  && $for== 'parent') {

                        $html .= "\n" . ' @foreach($children as $child)';
                        $html .= "\n" . ' <li class="sub">';
                        $html .= "\n" . ' <a href="{{ validRouteUrl(\'' . $permission->route . '\', $child->id) }}" class="{{ spn_active_link(\'' . $permission->route . '\') }}">';
                        $html .= "\n" . '{{ __(\'' . ($permission->lang_name ?? $permission->name) . '\') }} - {{ $child->full_name }}';
                        $html .= "\n" . ' </a>';
                        $html .= "\n" . ' </li>';

                        $html .= "\n" . " @endforeach";
                    } else {
                        $html .= "\n" . ' <li class="sub">';
                        $html .= "\n" . ' <a href="{{ validRouteUrl(\'' . $permission->route . '\') }}" class="{{ spn_active_link(\'' . $permission->route . '\') }}">';
                        $html .= "\n" . '{{ __(\'' . ($permission->lang_name ?? $permission->name) . '\') }}';
                        $html .= "\n" . ' </a>';
                        $html .= "\n" . ' </li>';
                    }

                    $html .= "\n" . " @endif";
                    if(in_array($permission->route, $excludeFeesRouteList)) {
                        $html .= "\n" . " @endif";
                    }
                }


                $html .= "\n" . ' </ul>';
            }

            if($alternate_module){
                $html .= "\n" . " @endif";
            }
            if($permission->module == 'Fees') {
                $html .= "\n" . " @endif";
            }
            if($permission->module == 'fees_collection') {
                $html .= "\n" . " @endif";
            }


            $html .= "\n" . ' </li>';
            $html .= "\n" . " @endif";
        }
        return $html;
    }
}
