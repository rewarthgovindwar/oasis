<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmSetupAdmin;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmSetupAdminController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $admin_setups = SmSetupAdmin::where('active_status', '=', 1)->get();
            $admin_setups = $admin_setups->groupBy('type');

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['admin_setups'] = $admin_setups->toArray();
                $data['admin_setups'] = $admin_setups->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.admin.setup_admin', ['admin_setups' => $admin_setups]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required',
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smSetupAdmin = new SmSetupAdmin();
            $smSetupAdmin->type = $request->type;
            $smSetupAdmin->name = $request->name;
            $smSetupAdmin->description = $request->description;
            $result = $smSetupAdmin->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Admin  Setup has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        try {
            $admin_setup = SmSetupAdmin::find($id);
            $admin_setups = SmSetupAdmin::where('active_status', '=', 1)->get();
            $admin_setups = $admin_setups->distinct('type');

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['admin_setup'] = $admin_setup->toArray();
                $data['admin_setups'] = $admin_setups->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.admin.setup_admin', ['admin_setups' => $admin_setups, 'admin_setup' => $admin_setup]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                'type' => 'required',
                'name' => 'required|max:100',
            ]);

            if ($validator->fails()) {
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $setup = SmSetupAdmin::find($id);
            $setup->type = $request->type;
            $setup->name = $request->name;
            $setup->description = $request->description;
            $result = $setup->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Admin Setup has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('setup-admin');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        try {
            // $id_key='class_id';

            // $tables=tableList::getTableList($id_key);

            // try {
            //     $delete_query = SmSetupAdmin::destroy($request->id);
            //     if(ApiBaseMethod::checkUrl($request->fullUrl())){
            //         if($delete_query){
            //             return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
            //         }else{
            //             return ApiBaseMethod::sendError('Something went wrong, please try again.');
            //         }
            //     }else{
            //         if($delete_query){
            //             return redirect()->back()->with('message-success-delete', 'Class has been deleted successfully');
            //         }else{
            //             return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
            //         }
            //     }

            // } catch (\Illuminate\Database\QueryException $e) {
            //     $msg='This data already used in  : '.$tables.' Please remove those data first';

            //     return redirect()->back()->with('message-danger-delete', $msg);
            // } catch (\Exception $e) {
            //     return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
            // }

            // $result = SmSetupAdmin::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($request) {
                    return ApiBaseMethod::sendResponse(null, 'Admin Setup can not delete');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($request) {
                Toastr::success('Operation successful', 'Success');

                return redirect('setup-admin');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
