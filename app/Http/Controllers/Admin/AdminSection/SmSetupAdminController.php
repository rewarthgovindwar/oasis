<?php

namespace App\Http\Controllers\Admin\AdminSection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSection\SmAdminSetupRequest;
use App\SmSetupAdmin;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmSetupAdminController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            $admin_setups = SmSetupAdmin::select(['id', 'type', 'name', 'description', 'active_status', 'updated_by', 'created_by'])->get();
            $admin_setups = $admin_setups->groupBy('type');

            return view('backEnd.admin.setup_admin', ['admin_setups' => $admin_setups]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAdminSetupRequest $smAdminSetupRequest)
    {
        /*
        try {
        */
            $user = Auth::user();
            $smSetupAdmin = new SmSetupAdmin();
            $smSetupAdmin->type = $smAdminSetupRequest->type;
            $smSetupAdmin->name = $smAdminSetupRequest->name;
            $smSetupAdmin->description = $smAdminSetupRequest->description;
            $smSetupAdmin->school_id = $user->school_id;
            if (moduleStatusCheck('University')) {
                $smSetupAdmin->un_academic_id = getAcademicId();
            } else {
                $smSetupAdmin->academic_id = getAcademicId();
            }

            $smSetupAdmin->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $admin_setup = SmSetupAdmin::find($id);
            $admin_setups = SmSetupAdmin::select(['id', 'type', 'name', 'description', 'active_status', 'updated_by', 'created_by'])->get();
            $admin_setups = $admin_setups->groupBy('type');

            return view('backEnd.admin.setup_admin', ['admin_setups' => $admin_setups, 'admin_setup' => $admin_setup]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmAdminSetupRequest $smAdminSetupRequest, $id)
    {
        /*
        try {
        */
            $setup = SmSetupAdmin::find($id);
            $setup->type = $smAdminSetupRequest->type;
            $setup->name = $smAdminSetupRequest->name;
            $setup->description = $smAdminSetupRequest->description;
            if (moduleStatusCheck('University')) {
                $setup->un_academic_id = getAcademicId();
            }

            $setup->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('setup-admin');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        /*
        try {
        */
            $tables1 = tableList::getTableList('complaint_type', $id);
            $tables2 = tableList::getTableList('complaint_source', $id);
            $tables3 = tableList::getTableList('source', $id);
            $tables4 = tableList::getTableList('reference', $id);
            if (!$tables1 && !$tables2  && !$tables3 && !$tables4 ) {
                 SmSetupAdmin::destroy($id);
            } else {
                $msg = 'This data already used in  : '.$tables1.' '.$tables2.' '.$tables3.' '.$tables4.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
