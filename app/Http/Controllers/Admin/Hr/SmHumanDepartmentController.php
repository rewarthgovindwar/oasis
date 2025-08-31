<?php

namespace App\Http\Controllers\Admin\Hr;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hr\SmDepartmentRequest;
use App\SmHumanDepartment;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmHumanDepartmentController extends Controller
{
    public function index(Request $request)
    {

        /*
        try {
        */
            $departments = SmHumanDepartment::select(['id', 'name'])->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($departments, null);
            }

            return view('backEnd.humanResource.human_resource_department', ['departments' => $departments]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmDepartmentRequest $smDepartmentRequest)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }

        // }
        /*
        try {
        */
            $smHumanDepartment = new SmHumanDepartment();
            $smHumanDepartment->name = $smDepartmentRequest->name;
            $smHumanDepartment->school_id = Auth::user()->school_id;
            $result = $smHumanDepartment->save();

            if (ApiBaseMethod::checkUrl($smDepartmentRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Department has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

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

    public function show(Request $request, $id)
    {

        /*
        try {
        */    $department = SmHumanDepartment::find($id);
            $departments = SmHumanDepartment::select(['id', 'name'])->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['department'] = $department->toArray();
                $data['departments'] = $departments->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.human_resource_department', ['department' => $department, 'departments' => $departments]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmDepartmentRequest $smDepartmentRequest, $id)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }

        // }

        /*
        try {
        */

            $department = SmHumanDepartment::find($smDepartmentRequest->id);

            $department->name = $smDepartmentRequest->name;
            $result = $department->save();

            if (ApiBaseMethod::checkUrl($smDepartmentRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Department has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect('department');
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
            $tables = \App\tableList::getTableList('department_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    $department = SmHumanDepartment::destroy($id);
                  
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($department) {
                                return ApiBaseMethod::sendResponse(null, 'Deleted successfully');
                            } else {
                                return ApiBaseMethod::sendError('Something went wrong, please try again');
                            }
                        } 
                        Toastr::success('Operation successful', 'Success');
                        return redirect()->back();
                   
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            } catch (Exception $e) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
