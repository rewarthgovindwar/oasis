<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use Exception;
use App\tableList;
use App\SmBaseGroup;
use App\SmBaseSetup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\GeneralSettings\SmBaseSetupRequest;

class SmBaseSetupController extends Controller
{


    public function index()
    {

        $base_groups = SmBaseGroup::where('active_status', '=', 1)->get();
        return view('backEnd.systemSettings.baseSetup.base_setup', ['base_groups' => $base_groups]);
    }

    public function store(SmBaseSetupRequest $smBaseSetupRequest)
    {

        /*
		try {
		*/

            $smBaseSetup = new SmBaseSetup();
            $smBaseSetup->base_setup_name = $smBaseSetupRequest->name;
            $smBaseSetup->base_group_id = $smBaseSetupRequest->base_group;
            $smBaseSetup->school_id = Auth::user()->school_id;
            $smBaseSetup->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

     }

    public function edit($id)
    {

        /*
		try {
		*/
            $base_setup = SmBaseSetup::find($id);
            $base_groups = SmBaseGroup::where('active_status', '=', 1)->get();

            return view('backEnd.systemSettings.baseSetup.base_setup', ['base_setup' => $base_setup, 'base_groups' => $base_groups]);
        /*
		} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    */
	}

    public function update(SmBaseSetupRequest $smBaseSetupRequest)
    {

        /*
		try {
		*/
            $base_setup = SmBaseSetup::find($smBaseSetupRequest->id);

            $base_setup->base_setup_name = $smBaseSetupRequest->name;
            $base_setup->base_group_id = $smBaseSetupRequest->base_group;
            $base_setup->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('base-setup');
        /*
		} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    */
	}

    public function delete(Request $request)
    {


            $tables = tableList::getTableList('bloodgroup_id', $request->id);
            $tables1 = tableList::getTableList('gender_id', $request->id);
            $tables2 = tableList::getTableList('religion_id', $request->id);
            if (empty($tables) && empty($tables1) && empty($tables2)) {
                SmBaseSetup::destroy($request->id);
                Toastr::success('Operation successful', 'Success');
                return redirect('base-setup');
            }
            $msg = 'This data already used in  : '.$tables.$tables1.$tables2.' Please remove those data first';
            Toastr::error($msg, 'Failed');
            return redirect()->back();

	}
}
