<?php

namespace App\Http\Controllers\Admin\Hr;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hr\SmDesignationRequest;
use App\SmDesignation;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmDesignationController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
            $designations = SmDesignation::select(['title', 'id', 'active_status', 'is_saas'])->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($designations, null);
            }

            return view('backEnd.humanResource.designation', ['designations' => $designations]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmDesignationRequest $smDesignationRequest)
    {

        /*
        try {
        */
            $smDesignation = new SmDesignation();
            $smDesignation->title = $smDesignationRequest->title;
            $smDesignation->school_id = Auth::user()->school_id;
            $result = $smDesignation->save();

            if (ApiBaseMethod::checkUrl($smDesignationRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Designation has been created successfully');
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        /*
        try {
        */
            // $designation = SmDesignation::find($id);
            $designation = SmDesignation::find($id);
            $designations = SmDesignation::select(['title', 'id', 'active_status', 'is_saas'])->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['designation'] = $designation->toArray();
                $data['designations'] = $designations->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.designation', ['designation' => $designation, 'designations' => $designations]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmDesignationRequest $smDesignationRequest, $id)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }

        // }
        // school wise uquine validation

        /*
        try {
        */
            // $designation = SmDesignation::find($request->id);
            $designation = SmDesignation::find($smDesignationRequest->id);
            $designation->title = $smDesignationRequest->title;
            $result = $designation->save();

            if (ApiBaseMethod::checkUrl($smDesignationRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Designation has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect('designation');
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
            $tables = \App\tableList::getTableList('designation_id', $id);
            // return $tables;
            /*
            try {
            */
                if ($tables == null) {

                    $designation = SmDesignation::destroy($id);
                    if ($designation) {
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($designation) {
                                return ApiBaseMethod::sendResponse(null, 'Deleted successfully');
                            }

                            return ApiBaseMethod::sendError('Something went wrong, please try again');

                        }

                        Toastr::success('Operation successful', 'Success');

                        return redirect()->back();
                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
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
