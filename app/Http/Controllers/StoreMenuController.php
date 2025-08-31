<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\MenuManage\Entities\SmMenu;
use Modules\RolePermission\Entities\Permission;

class StoreMenuController extends Controller
{
   public function index()
    {
        SmMenu::query()->truncate();
        $sidebarFile = base_path('Modules/MenuManage/Resources/var/sidebars.sql');
            if(file_exists($sidebarFile))
            {
                $hasData = DB::table('sidebars')->count();
                if($hasData == 0)
                {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    DB::unprepared(file_get_contents($sidebarFile));
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            }
        try{
            $this->adminMenu();
            $this->studentMenu();
            $this->parentMenu();
            $this->removeMenuManage();
        }catch(Exception  $e){
            Log::error($e->getMessage());
        }
        
    }

   public function parentMenu()
    {   
       
            $rend = date("ds");        
            $parent_section = $this->insertMenu([
                "name" => "",
                "route" => "",
                "module" => "",
                "lang_name" => "",
                "icon" => "",
                "status" => "",
                "is_saas" => 1,
                "role_id" => 3,
                "is_alumni" => null,
                "position" => 1,
                "school_id" => getSchool()->id,
                'menu_status' => 1,
                'permission_section' => 1,
                "parent" => $rend,
                "permission_id" => $rend
            ]);
            $menus = DB::table('permissions')->where('type',1)
                                            ->where('is_parent',1)
                                            ->orderBy('position','ASC')
                                            ->get();
            foreach($menus as $menu)
            {   
                $menu_insert = $this->insertMenu([
                    "name" => $menu->name,
                    "route" => $menu->route,
                    "module" => $menu->module,
                    "lang_name" => $menu->lang_name,
                    "icon" => $menu->icon,
                    "status" => $menu->status,
                    "is_saas" => $menu->is_saas,
                    "school_id" => getSchool()->id,
                    'menu_status' => 1,
                    'permission_section' => 0,
                    "parent_id" => $parent_section->id,
                    "role_id" => 3,
                    "parent" => $parent_section->id,
                    "permission_id" => $parent_section->id
                ]);
                $sub_menus = DB::table('permissions')->where('type',2)
                                        ->where('is_parent',1)
                                        ->where('parent_route',$menu->route)
                                        ->orderBy('position','ASC')
                                        ->get();
                foreach($sub_menus as $sub)
                {
                    $hasAlready = DB::table('sm_menus')->where('role_id',3)->where('route',$sub->route)->first();
                    if(!$hasAlready){
                         $this->insertMenu([
                            "name" => $sub->name,
                            "route" => $sub->route,
                            "module" => $sub->module,
                            "lang_name" => $sub->lang_name,
                            "icon" => $sub->icon,
                            "status" => $sub->status,
                            "is_saas" => $sub->is_saas,
                            "role_id" => 3,
                            "school_id" => getSchool()->id,
                            'menu_status' => 1,
                            'permission_section' => 0,
                            "parent_id" => $menu_insert->id,   
                            "parent" => $menu_insert,
                            "permission_id" => $menu_insert                         
                        ]);
                    }
                   
                }
                
            }

             $lms = DB::table('sm_menus')->where('route','lms_menu')->where('role_id',3)->first();
             if($lms){
                DB::table('sm_menus')->whereIn('route',['lms.student-all-courses','lms.enrolledCourse','lms.student.purchaseLog'])
                                    ->where('role_id',3)
                                    ->update(['parent_id' => $lms->id]);
             }

            $lesson = DB::table('sm_menus')->where('route','lesson-plan')->where('role_id',3)->first();
            if($lesson){
                 DB::table('sm_menus')->where('role_id',3)->WhereIn('route',['lesson-parent-lessonPlan','lesson-parent-lessonPlan-overview'])->update(['parent_id' => $lesson->id]);
            }
            $remove = ['parent_pdf_exam','parent_view_pdf_result','om_parent_online_examination','om_parent_online_examination_result'];
            DB::table('sm_menus')->whereIn('route',$remove)->where('role_id',3)->delete();
        
    }

    public function studentMenu()
    {
        
        $rend = date("dm");
        $student_section = $this->insertMenu([
            "name" => "",
            "route" => "",
            "module" => "",
            "lang_name" => "",
            "icon" => "",
            "status" => "",
            "is_saas" => 1,
            "role_id" => 2,
            "is_alumni" => null,
            "position" => 1,
            "school_id" => getSchool()->id,
            'menu_status' => 1,
            'permission_section' => 1,
            "permission_id" => $rend,
            "parent" => $rend
            
        ]);
        $menus = DB::table('permissions')->where('type',1)->where('is_student',1)->get();
        foreach($menus as $menu)
        {   
            $menu_insert = $this->insertMenu([
                "name" => $menu->name,
                "route" => $menu->route,
                "module" => $menu->module,
                "lang_name" => $menu->lang_name,
                "icon" => $menu->icon,
                "status" => $menu->status,
                "is_saas" => $menu->is_saas,
                "school_id" => getSchool()->id,
                'menu_status' => 1,
                'permission_section' => 0,
                "parent_id" => $student_section->id,
                "role_id" => 2,
                "permission_id" => $rend,
                "parent" => $rend
            ]);
            $sub_menus = DB::table('permissions')->where('type',2)->where('is_student',1)->where('parent_route',$menu->route)->orderBy('position','ASC')->get();
            foreach($sub_menus as $sub)
            {
                $this->insertMenu([
                    "name" => $sub->name,
                    "route" => $sub->route,
                    "module" => $sub->module,
                    "lang_name" => $sub->lang_name,
                    "icon" => $sub->icon,
                    "status" => $sub->status,
                    "is_saas" => $sub->is_saas,
                    "role_id" => 2,
                    "school_id" => getSchool()->id,
                    'menu_status' => 1,
                    'permission_section' => 0,
                    "parent_id" => $menu_insert->id,    
                    "permission_id" => $rend,
                    "parent" => $rend                        
                ]);
            }
        }
        $lesson_fix = DB::table('sm_menus')->where('route','lesson-plan')->where('role_id',2)->first();
        if($lesson_fix)
        {
            DB::table('sm_menus')->where('role_id',2)->whereIn('route',['lesson-student-lessonPlan','lesson-student-lessonPlan-overview'])->update(['parent_id' => $lesson_fix->id]);
        }

        
        $lms = DB::table('sm_menus')->where('route','lms_menu')->where('role_id',2)->first();
        if($lms){
            DB::table('sm_menus')->where('role_id',2)->whereIn('route',['lms.student.certificate','lms.student.quiz','lms.student-purchaseLog','lms.student-all-courses','lms.student-enrolledCourse'])->update(['parent_id' => $lms->id]);
        }
         $removeable_routes = $this->removeAbleRoutes();
        DB::table('sm_menus')->where('role_id',2)->whereIn('route',$removeable_routes)->delete();
    
    
    }

    public function adminMenu()
    {
        $sections = [
            "dashboard_section",
            "administration_section",
            "student_section",
            "exam_section",
            "hr_section",
            "accounts_section",
            "utilities_section",
            "report_section",
            "settings_section",
            "module_section"
        ];

        $i  = 0;
        foreach($sections as $section){
            $sectionPeremission = DB::table('permissions')->where('route',$section)->first();
            $i++;
            $section_insert = $this->insertMenu([
                "name" => $sectionPeremission->name,
                "route" => $sectionPeremission->route,
                "module" => $sectionPeremission->module,
                "lang_name" => $sectionPeremission->lang_name,
                "icon" => $sectionPeremission->icon,
                "status" => $sectionPeremission->status,
                "is_saas" => $sectionPeremission->is_saas,
                "role_id" => 1,
                "is_alumni" => $sectionPeremission->is_alumni,
                "position" => $i,
                "school_id" => getSchool()->id,
                'menu_status' => $sectionPeremission->menu_status,
                'permission_section' => 1,
                "permission_id" => $sectionPeremission->id,
                "parent" => $sectionPeremission->id,
            ]);
            if($sectionPeremission){
                $sidebar = DB::table('sidebars')->where('permission_id',$sectionPeremission->id)->first();
                if($sidebar)
                {   

                    $menus = DB::table('sidebars')
                             ->where('parent',$sidebar->permission_id)
                             ->orderBy('position','ASC')
                             ->get();
                    foreach($menus as $menu){                        
                        $menuPermission= DB::table('permissions')->where('id',$menu->permission_id)->first();                        
                        if($menuPermission){
                            $menu_insert = $this->insertMenu([
                                "name" => $menuPermission->name,
                                "route" => $menuPermission->route,
                                "module" => $menuPermission->module,
                                "lang_name" => $menuPermission->lang_name,
                                "icon" => $menuPermission->icon,
                                "status" => $menuPermission->status,
                                "is_saas" => $menuPermission->is_saas,
                                "school_id" => getSchool()->id,
                                'menu_status' => 1,
                                'permission_section' => 0,
                                "parent_id" => $section_insert->id,
                                "role_id" => 1,
                                "permission_id" => $sectionPeremission->id,
                                "parent" => $sectionPeremission->id,
                            ]);
                            $thids = DB::table('permissions')->whereNotNull('sidebar_menu')->where('parent_route',$menuPermission->route)->orderBy('position','ASC')->get();
                                foreach($thids as $third){
                                    $this->insertMenu([
                                        "name" => $third->name,
                                        "route" => $third->route,
                                        "module" => $third->module,
                                        "lang_name" => $third->lang_name,
                                        "icon" => $third->icon,
                                        "status" => $third->status,
                                        "is_saas" => $third->is_saas,
                                        "role_id" => 1,
                                        "school_id" => getSchool()->id,
                                        'menu_status' => 1,
                                        'permission_section' => 0,
                                        "parent_id" => $menu_insert->id,
                                        "permission_id" => $third->id,
                                        "parent" => $sectionPeremission->id,
                                    ]);
                            }
                        }
                    }
                    DB::table('sm_menus')->where('route','qr_code_attendance')->delete();
                }
                if($section == 'student_section'){
                    $record = DB::table('sm_menus')->where('route','behaviour_records')->where('role_id',1)->first();
                    
                    $behave = DB::table('permissions')->where('parent_route',$record->route)->whereNotIn('route',['behaviour_records.incident_comment'])->where('type',2)->where('is_admin',1)->get();
                    foreach($behave as $be){
                        $this->insertMenu([
                            "name" => $be->name,
                            "route" => $be->route,
                            "module" => $be->module,
                            "lang_name" => $be->lang_name,
                            "icon" => $be->icon,
                            "status" => $be->status,
                            "is_saas" => $be->is_saas,
                            "role_id" => 1,
                            "school_id" => getSchool()->id,
                            'menu_status' => 1,
                            'permission_section' => 0,
                            "parent_id" => $record->id,
                            "permission_id" => $third->id,
                            "parent" => $sectionPeremission->id,
                        ]);
                    }
                }
                if($section == 'module_section')
                {                
                    $this->moduleRoute($section_insert,'admin');
                    DB::table('sm_menus')->where('route','g-meet.parent.virtual-class')->where('role_id',1)->delete();
                }
                $this->manualUpdate();
                DB::table('sm_menus')->where('module','OnlineExam')->delete();
            }
            
        }
    }

    public function insertMenu($menu)
    {
       return SmMenu::create($menu);
    }

    public function moduleRoute($section_id = null, $for)
    {
         $this->adminModule($section_id);
    }

    public function adminModule($section_id)
    {
        $menus = DB::table('permissions')->whereNotNull('module')->whereNotIn('module',['Fees','BehaviourRecords','fees_collection','DownloadCenter','Saas'])->where('type',1)->where('is_admin',1)->get();
        foreach($menus as $menu)
        {
            $menu_insert = $this->insertMenu([
                "name" => $menu->name,
                "route" => $menu->route,
                "module" => $menu->module,
                "lang_name" => $menu->lang_name,
                "icon" => $menu->icon,
                "status" => $menu->status,
                "is_saas" => $menu->is_saas,
                "school_id" => getSchool()->id,
                'menu_status' => 1,
                'permission_section' => 0,
                "parent_id" => $section_id->id,
                "role_id" => 1,
                "permission_id" => $section_id->id,
                "parent" => $section_id->id,
            ]);
            $thids = DB::table('permissions')->where('parent_route',$menu->route)->where('type',2)->orderBy('position','ASC')->get();            
                    foreach($thids as $third){
                        $this->insertMenu([
                            "name" => $third->name,
                            "route" => $third->route,
                            "module" => $third->module,
                            "lang_name" => $third->lang_name,
                            "icon" => $third->icon,
                            "status" => $third->status,
                            "is_saas" => $third->is_saas,
                            "role_id" => 1,
                            "school_id" => getSchool()->id,
                            'menu_status' => 1,
                            'permission_section' => 0,
                            "parent_id" => $menu_insert->id,
                            "permission_id" => $third->id,
                            "parent" => $menu_insert->id,
                        ]);
            }
        }
    }

    public function removeMenuManage()
    {
        DB::table('sm_menus')->where('route','menumanage.index')->whereIn('role_id',[2,3])->delete();
        DB::table('permissions')->where('route','menumanage.index')->where('is_parent',1)->delete();
        DB::table('permissions')->where('route','menumanage.index')->where('is_student',1)->delete();
    }

    public function removeAbleRoutes()
    {
        return [
            'student-profile.fees',
            'student-profile.profile',
            'student-profile.exam',
            'student-profile.document',
            'studentTimeline',
            'student_homework_view',
            'add-content',
            'parent_view_pdf_result',
            'parent_pdf_exam'
        ];
    }


    public function manualUpdate()
    {
       
            $parent = DB::table('sm_menus')->where('route','download-center')->first();
            if($parent)
            {
                $permissions = DB::table('permissions')->where('parent_route',$parent->route)->where('type',2)->where('is_admin',1)->get();
                foreach($permissions as $permission)
                {
                     $hasOne = DB::table('sm_menus')->where('route',$parent->route)->where('role_id',1)->get();
                     if(!$hasOne)
                     {
                        $this->insertMenu([
                            "name" => $permission->name,
                            "route" => $permission->route,
                            "module" => $permission->module,
                            "lang_name" => $permission->lang_name,
                            "icon" => $permission->icon,
                            "status" => $permission->status,
                            "is_saas" => $permission->is_saas,
                            "role_id" => 1,
                            "school_id" => getSchool()->id,
                            'menu_status' => 1,
                            'permission_section' => 0,
                            "parent_id" => $parent->id,
                            "permission_id" => $permission->id,
                            "parent" => $parent->id,
                        ]);
                     }
                        
                }
            }

            $parent = DB::table('sm_menus')->where('route','accounts_report')->first();
            if($parent)
            {
                $permissions = DB::table('permissions')->where('parent_route',$parent->route)->where('is_admin',1)->get();
                
                foreach($permissions as $permission)
                {
                     $hasOne = DB::table('sm_menus')->where('route',$parent->route)->where('role_id',1)->get();
                        $this->insertMenu([
                            "name" => $permission->name,
                            "route" => $permission->route,
                            "module" => $permission->module,
                            "lang_name" => $permission->lang_name,
                            "icon" => $permission->icon,
                            "status" => $permission->status,
                            "is_saas" => $permission->is_saas,
                            "role_id" => 1,
                            "school_id" => getSchool()->id,
                            'menu_status' => 1,
                            'permission_section' => 0,
                            "parent_id" => $parent->id,
                            "permission_id" => $permission->id,
                            "parent" => $parent->id,
                        ]);
                }
            }

            DB::table('sm_menus')->where('route','zoom')->update(['lang_name' => 'common.zoom']);
        
    }
}
