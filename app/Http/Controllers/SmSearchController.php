<?php

namespace App\Http\Controllers;

use Exception;
use App\SmStudent;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Route;
use Modules\MenuManage\Entities\SmMenu;
use Modules\MenuManage\Entities\Sidebar;
use Modules\RolePermission\Entities\Permission;

class SmSearchController extends Controller
{

    public function search(Request $request)
    {
        try {
            if ($request->ajax()) {
                $output = '';
                $query = $request->get('search');
                if ($query != '') {
                    if(auth()->user()->role_id == 2){
                        $role_id = 2;
                    }elseif(auth()->user()->role_id == 3){
                        $role_id = 3;
                    }else{
                        $role_id = 1;
                    }
                    $menus = SmMenu::where('name','LIKE','%'.$query.'%')->where('role_id', $role_id)->where('status',1)->get();
                    $urls = [];
                    
                    foreach($menus as $menu)
                    {
                        if(!empty($menu->module) && moduleStatusCheck($menu->module))
                        {
                            if(userPermission($menu->route))
                            {
                                $urls[] =  [
                                    "name" => $menu->name,
                                    "route" => $menu->route
                                ] ;
                            }
                        }else{
                             if(userPermission($menu->route))  {
                                $urls[] =  [
                                    "name" => $menu->name,
                                    "route" => $menu->route
                                ] ;
                            }
                        }
                    }

                    return $urls;
                } else {
                    return response()->json(['not found' => 'Not Foound'], 404);
                }
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        return null;
    }

    public function dashboardStudentSearch(Request $request)
    {
        try {
            if (is_string($request->search)) {
                $nameOrAdmissionNo = $request->search;
            }

            if (preg_match('~\d+~', $request->search)) {
                $nameOrAdmissionNo = (int) $request->search;
            }

            return SmStudent::when(is_numeric($nameOrAdmissionNo), function ($q) use ($nameOrAdmissionNo): void {
                $q->where('admission_no', $nameOrAdmissionNo);
            })
                ->when(is_string($nameOrAdmissionNo), function ($q) use ($nameOrAdmissionNo): void {
                    $q->where('full_name', 'like', '%'.$nameOrAdmissionNo.'%');
                })
                ->get()
                ->map(function ($value): array {
                    return [
                        'name' => $value->full_name,
                        'route' => route('student_view', $value->id),
                    ];
                });
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
