<?php

namespace App\Http\Controllers\Admin\RolePermission;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Role;
use App\SmModule;
use App\SmRolePermission;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\RolePermission\Entities\InfixRole;

class SmRolePermissionController extends Controller
{
    public function assignPermission(Request $request, $id)
    {

        /*
        try{
        */
        // $role = InfixRole::find($id);
        if (checkAdmin()) {
            $role = InfixRole::find($id);
        } else {
            $role = InfixRole::where('is_saas', 0)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
        }
        $modulesRole = SmModule::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
        $role_permissions = SmRolePermission::where('role_id', $id)->where('school_id', Auth::user()->school_id)->get();
        $already_assigned = [];
        foreach ($role_permissions as $role_permission) {
            $already_assigned[] = $role_permission->module_link_id;
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['role'] = $role;
            $data['modules'] = $modulesRole->toArray();
            $data['already_assigned'] = $already_assigned;

            return ApiBaseMethod::sendResponse($data, null);
        }

        return view('backEnd.systemSettings.role.assign_role_permission', ['role' => $role, 'modulesRole' => $modulesRole, 'already_assigned' => $already_assigned]);
        /*} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/
    }

    public function rolePermissionStore(Request $request)
    {
        /*
        try{
        */

        // SmRolePermission::where('role_id', $request->role_id)->delete();
        if (checkAdmin()) {
            SmRolePermission::where('role_id', $request->role_id)->delete();
        } else {
            SmRolePermission::where('role_id', $request->role_id)->where('school_id', Auth::user()->school_id)->delete();
        }

        if (property_exists($request, 'permissions') && $request->permissions !== null) {
            foreach ($request->permissions as $permission) {
                $role_permission = new SmRolePermission();
                $role_permission->role_id = $request->role_id;
                $role_permission->module_link_id = $permission;
                $role_permission->school_id = Auth::user()->school_id;
                $role_permission->save();
            }
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse(null, 'Role permission has been assigned successfully');
        }

        Toastr::success('Role permission has been assigned successfully', 'Success');

        return redirect()->back();
        // return redirect('role')->with('message-success-delete', 'Role permission has been assigned successfully');
        /*} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/
    }
}
