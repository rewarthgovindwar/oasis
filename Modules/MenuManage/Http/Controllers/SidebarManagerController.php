<?php

namespace Modules\MenuManage\Http\Controllers;

use Exception;
use App\GlobalVariable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\SidebarDataStore;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Cache;
use Modules\MenuManage\Entities\SmMenu;
use Modules\MenuManage\Entities\Sidebar;
use Modules\RolePermission\Entities\InfixRole;
use Modules\RolePermission\Entities\Permission;
use Modules\MenuManage\Http\Requests\SectionRequestFrom;

class SidebarManagerController extends Controller
{
    use SidebarDataStore;

    public function __construct() {}

    public static function unUsedMenu($role_id = null)
    {
        $sectionIds = Sidebar::whereNull('parent')->pluck('permission_id')->toArray();

        $parentSidebars = Sidebar::whereIn('parent', $sectionIds)
            ->deActiveMenuUser($role_id)
            ->pluck('permission_id')
            ->toArray();

        $single = Sidebar::whereNotIn('parent', $parentSidebars)
            ->deActiveMenuUser($role_id)
            ->pluck('permission_id')
            ->toArray();
        $hasIds = array_merge($parentSidebars, $single);

        $hasIds = (array_unique($hasIds));
        if ($hasIds !== []) {
            return Sidebar::whereIn('permission_id', $hasIds)->deActiveMenuUser($role_id)->get();
        }

        return collect();
    }

    public function sectionStore(Request $request)
    {

        $role_id = $this->getRoleId($request->role_name);
        if (config('app.app_sync')) {
            Toastr::error('Restricted in demo mode');
            return back();
        }
        $request->validate([
            'name' => ['required', Rule::unique('permissions', 'name')->where('id', $role_id)],
        ]);
        
        $permission_position = SmMenu::where('permission_section',1)->where('role_id',$role_id)->orderBy('position','DESC')->first();
        $position = ($permission_position ? $permission_position->position : 0) + 1;
        $role_slug = str_replace('-','_',Str::slug(mb_strtolower($request->name)));
        SmMenu::create([
            "name" => $request->name,
            "route" => $role_slug,
            "lang_name" => $request->name,
            "is_saas" => 1,
            "role_id" => $role_id,
            "is_alumni" => null,
            "position" => $position,
            "school_id" => getSchool()->id,
            'menu_status' => 1,
            'permission_section' => 1
        ]);
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('menumanage.index', ['role_name' => $request->role_name]);
    }

    public function sectionEditForm(Request $request, $id)
    {
        if (config('app.app_sync')) {
            Toastr::error('Restricted in demo mode');
            return back();
        }
        if(!empty($request->role_name))
        {
            $role_name = $request->role_name;
        }else{
            if(auth()->user()->role_id == 2)
            {
                $role_name = 'student';
            }elseif(auth()->user()->role_id == 3){
                $role_name = 'parent';
            }else{
                $role_name = 'staff';
            }
        }

        $data = [];
        $role_id = $request->role_id;
        $data['editPermissionSection'] = SmMenu::where('id',$id)->first();

        $data['unused_menus'] = self::unUsedMenu($role_name);
        Cache::forget(sidebar_cache_key($role_id));
        $data['sidebar_menus'] = getMenus($role_name);

        if ($role_id) {
            $data['role'] = InfixRole::find($role_id);
        }
        $data['role_name'] = $role_name;
        $view = $role_id ? 'menumanage::role_index' : 'menumanage::index';

        return view($view, $data);
    }

    public function sectionUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        $section = SmMenu::find($request->id);
        $section->name = $request->name;
        $section->lang_name = $request->name;
        $section->save();
        Toastr::success('Operation successful', 'Success');
        $route = route('menumanage.index',['role_name' => $request->role_name]);
        return redirect()->to($route);
    }

    public function deleteSection(Request $request)
    {
        
        if (config('app.app_sync')) {
            return $this->reloadWithData();
        }

        try {

            if ($request->id !== 1) {
                $role_id = $request->role_id;
                $is_role_based_sidebar = is_role_based_sidebar();
                $section = Sidebar::with('subModule')->where('id', $request->id)->when(! $is_role_based_sidebar, function ($q): void {
                    $q->where('user_id', auth()->user()->id);
                }, function ($q) use ($role_id): void {
                    $q->where('role_id', $role_id);
                })->first();
                if (count($section->subModule) !== 0) {

                    foreach ($section->subModule as $sidebar) {
                        $sidebar->update(['active_status' => 0]);
                    }
                }

                if ($section->permissionInfo->permission_section === 1 && count($section->subModule) === 0) {

                    Permission::when(! $is_role_based_sidebar, function ($q): void {
                        $q->where('user_id', auth()->user()->id);
                    }, function ($q) use ($role_id): void {
                        $q->where('role_id', $role_id);
                    })->where('id', $section->permission_id)->delete();
                    $section->delete();
                }

            }

            Cache::forget(sidebar_cache_key($role_id));

            return $this->reloadWithData();
        } catch (Exception $exception) {
            return response()->json([
                'msg' => __('common.Operation failed'),
            ], 500);
        }

    }

    public function removeSection(Request $request)
    {
        if (config('app.app_sync')) {
            return $this->reloadWithData();
        }
        if($request->id != 1){
            $role_id = $this->getRoleId($request->role_name);
            $menu = SmMenu::with(['childs' => function($q) use ($role_id){ $q->where('role_id',$role_id); }])
                           ->where('id',$request->id)
                           ->where('permission_section',1)
                           ->where('role_id',$role_id)
                           ->first();            
            if($menu->childs->count() > 0){
                foreach($menu->childs as $child){
                   $child->update(['menu_status' => 0]);
                }
            }
            $menu->delete();
        }
        return $this->reloadWithData();
    }

    public function removeMenu(Request $request)
    {

        $is_role_based_sidebar = is_role_based_sidebar();
        $role_id = $request->role_id;

        $sidebar = Sidebar::with(['userChildMenu' => function ($q) use ($role_id, $is_role_based_sidebar): void {
            $q->when($is_role_based_sidebar, function ($q) use ($role_id): void {
                $q->where('role_id', $role_id)->whereNull('user_id');
            });
        }])->where('id', $request->id)->when(! $is_role_based_sidebar, function ($q): void {
            $q->where('user_id', auth()->user()->id);
        }, function ($q) use ($role_id): void {
            $q->where('role_id', $role_id);
        })->first();
        if ($sidebar && ! config('app.app_sync')) {
            if ($sidebar->userChildMenu->count() > 0) {
                foreach ($sidebar->userChildMenu as $child) {
                    $child->update(['active_status' => 0]);
                }

            }

            Sidebar::where('parent', $sidebar->permission_id)->update(['active_status' => 0]);
            $sidebar->active_status = 0;
            $sidebar->save();
        }

        Cache::forget(sidebar_cache_key($role_id));

        return $this->reloadWithData();

    }

    public function menuRemove(Request $request)
    {
        $data =  $request->all();
        
        $menu =  SmMenu::where('id',$data['id'])->first();
        
        if($menu) {
            DB::table('sm_menus')->where('id',$data['id'])->update(['menu_status' => 0]);
        }
        return $this->reloadWithData();
    }

     public function menuUpdate(Request $request)
    {
       
        if (! config('app.app_sync')) {
            $menuItemOrder = json_decode($request->get('order'));

            if ($request->unused_ids) {
                SmMenu::whereIn('id', $request->unused_ids)->update([
                    'menu_status' => 0,
                ]);
            }

            if ($request->ids) {
                SmMenu::whereIn('id', $request->ids)->update([
                    'menu_status' => 1,
                ]);
            }

        }

        $this->orderMenu($menuItemOrder, $request->menu_status, $request->section, $request->un_used);

        Cache::forget(sidebar_cache_key($request->role_id));

        return $this->reloadWithData();
    }

    public function sortSection(Request $request): void
    {
        $role_id = $request->role_id;
        if ($request->ids && ! config('app.app_sync')) {
            foreach ($request->ids as $key => $permissionSection) {

                $sidebar = SmMenu::find($permissionSection);

                if ($sidebar) {
                    $sidebar->position = $key + 1;
                    $sidebar->save();
                }

            }
        }

        Cache::forget(sidebar_cache_key($role_id));
    }

    public function resetMenu(Request $request)
    {
       set_time_limit(120);
            $role_id = $request->role_id;
            if(!empty($request->role_name))
            {
                    $role_name = $request->role_name;
            }else{
                if(auth()->user()->role_id == 2)
                {
                    $role_name = 'student';
                }elseif(auth()->user()->role_id == 3){
                    $role_name = 'parent';
                }else{
                    $role_name = 'staff';
                }
            }

            $role_ids = $this->getRoleids($role_name);
            
            Sidebar::when($role_name == 'student', function ($q)  use ($role_ids) {
                $q->whereIn('role_id',$role_ids);
            })->when($role_name == 'parent', function ($q)  use ($role_ids) {
                $q->whereIn('role_id',$role_ids);
            })->when($role_name == 'staff', function ($q)  use ($role_ids) {
                $q->whereNotIn('role_id',$role_ids);
            })->delete();
            
            
            $this->resetSidebarStore($role_name);
            Cache::forget(sidebar_cache_key($role_name));
            return redirect()->back();

    }

    public function resetWithDefault()
    {
        try {
            Sidebar::where('user_id', auth()->user()->id)->where('role_id', auth()->user()->role_id)->delete();
            $this->defaultSidebarStore();
            return redirect()->back();
        } catch (Exception $exception) {

        }

        return null;
    }

    public function getMenusData($role_name): array
    {
        $unused_menus = getUnusedMenus($role_name);
        $sidebar_menus = getMenus($role_name);
        return ['unused_menus' => $unused_menus, 'sidebar_menus' => $sidebar_menus];
    }

     private function orderMenu(array $menuItems, $menu_status = 1, $parent_id = null, $un_used = null): void
    {

        foreach ($menuItems as $index => $item) {
            $menuItem = SmMenu::where('id', $item->id)
                ->when(! $un_used, function ($q): void {
                    $q->where('menu_status', 1);
                })
                ->first();

            $data = [
                'position' => $index + 1,
                'parent_id' => $parent_id,
                'menu_status' => $menu_status ?? 1,
            ];
            
            if ($menuItem) {
                $menuItem->update($data);
                if (isset($item->children)) {
                    $this->orderMenu($item->children, $menu_status, $menuItem->permission_id, $un_used);
                }
            }

        }

    }

    private function reloadWithData()
    {   

        if(!empty(request()->role_name)){
            $role_name = request()->role_name;
        }else{
            if(auth()->user()->role_id == 2)
            {
                $role_name = 'student';
            }elseif(auth()->user()->role_id == 3){
                $role_name = 'parent';
            }else{
                $role_name = 'staff';
            }
        }
        $data = $this->getMenusData($role_name);
        $data['role'] = InfixRole::find(request()->role_id); 
        $data['role_name'] = $role_name;
        return response()->json([
            'msg' => 'Success',
            'available_list' => (string) view('menumanage::components.available_list', $data),
            'menus' => (string) view('menumanage::components.components', $data),
            'live_preview' => (string) view('menumanage::components.live_preview', $data),
        ], 200);
    }

    public function getRoleids($role_name)
    {
        if($role_name == 'student'){
            $role_ids = [2];
        }elseif($role_name == 'parent')
        {
            $role_ids = [3];
        }else{
            $role_ids = [2,3];
        }

        return $role_ids;        
    }

    public function getRoleId($role_name = null)
    {
        if($role_name)
        {
            if($role_name == 'student')
            {
                return 2;
            }elseif($role_name == 'parent'){
                return 3;
            }else{
                return 1;
            }
        }else{
            if(auth()->user()->role_id == 2){
                return 2;
            }elseif(auth()->user()->role_id == 3){
                return 3;
            }else{
                return 1;
            }
        }
    }
}
