<?php

namespace Modules\MenuManage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\MenuManage\Entities\SmMenu;
use Illuminate\Contracts\Support\Renderable;

class SidebarResetController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $role_id = $this->getRole($request->role_name);
        $this->reset($role_id);
        Toastr::success(trans('common.Operation successful'),trans('common.success'));
        return back();
    }

    public function reset($role_id)
    {
        $menus = DB::table('default_menus')->where('role_id',$role_id)->get();
        DB::table('sm_menus')->where('role_id',$role_id)->where('school_id',getSchool()->id)->delete();
        foreach($menus as $menu){              
            $first_menu = SmMenu::create([
                "name" => $menu->name,
                "route" => $menu->route,
                "module" => $menu->module,
                "lang_name" => $menu->lang_name,
                "icon" => $menu->icon,
                "status" => $menu->status,
                "is_saas" => $menu->is_saas,
                "school_id" => auth()->user()->id,
                'menu_status' => 1,
                'permission_section' => $menu->permission_section,
                "position" => $menu->position,
                "default_position" => $menu->default_position,
                "role_id" => $menu->role_id,
                "permission_id" => $menu->permission_id,
                "parent" => $menu->parent_id,
                "parent_id" => $menu->parent_id,
            ]);
            $secound_menus = DB::table('default_menus')->where('parent_id',$menu->id)->get();  
            if(count($secound_menus) > 0){
                foreach($secound_menus as $secound)
                {
                    $secound_menu = SmMenu::create([
                        "name" => $secound->name,
                        "route" => $secound->route,
                        "module" => $secound->module,
                        "lang_name" => $secound->lang_name,
                        "icon" => $secound->icon,
                        "status" => $secound->status,
                        "is_saas" => $secound->is_saas,
                        "school_id" => auth()->user()->id,
                        'menu_status' => 1,
                        'permission_section' => $secound->permission_section,
                        "position" => $secound->position,
                        "default_position" => $secound->default_position,
                        "role_id" => $secound->role_id,
                        "permission_id" => $secound->permission_id,
                        "parent" => $first_menu->id,
                        "parent_id" => $first_menu->id,
                    ]);

                    $third_menus = DB::table('default_menus')->where('parent_id',$secound->id)->get();
                    if(count($third_menus) > 0){
                        foreach($third_menus as $third)
                        {
                            SmMenu::create([
                                "name" => $third->name,
                                "route" => $third->route,
                                "module" => $third->module,
                                "lang_name" => $third->lang_name,
                                "icon" => $third->icon,
                                "status" => $third->status,
                                "is_saas" => $third->is_saas,
                                "school_id" => auth()->user()->id,
                                'menu_status' => 1,
                                'permission_section' => $third->permission_section,
                                "position" => $third->position,
                                "default_position" => $third->default_position,
                                "role_id" => $third->role_id,
                                "permission_id" => $third->permission_id,
                                "parent" => $secound_menu->id,
                                "parent_id" => $secound_menu->id,
                            ]);
                        }
                    }
                }
            }   
        }
    }
    
    public function getRole($role_name)
    {
        if(!empty(request()->role_name))
        {
            if($role_name == 'student'){
                return 2;
            }elseif($role_name == 'parent'){
                return 3;
            }else{
                return 1;
            }

        }else{
            $user = Auth::user();
            if($user->role_id == 2)
            {
                return 2;
            }elseif($user->role_id == 3){
                return 3;
            }else{
                return 1;
            }
        }
    }

    
}
