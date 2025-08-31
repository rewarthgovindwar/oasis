<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmLeaveType;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmLeaveTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $leave_types = SmLeaveType::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($leave_types->toArray(), null);
            }

            return view('backEnd.humanResource.leave_type', ['leave_types' => $leave_types]);
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
            'type' => 'required|unique:sm_leave_types',

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
            $smLeaveType = new SmLeaveType();
            $smLeaveType->type = $request->type;
            $smLeaveType->total_days = $request->total_days;
            $result = $smLeaveType->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Type has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');
            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
                // return redirect()->back()->with('message-success', 'Type has been created successfully');
            }

            return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

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
            $leave_type = SmLeaveType::find($id);
            $leave_types = SmLeaveType::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leave_type'] = $leave_type->toArray();
                $data['leave_types'] = $leave_types->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.humanResource.leave_type', ['leave_types' => $leave_types, 'leave_type' => $leave_type]);
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
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'type' => 'required|unique:sm_leave_types,type,'.$request->id,
                'id' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'type' => 'required|unique:sm_leave_types,type,'.$request->id,
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $leave_type = SmLeaveType::find($request->id);
            $leave_type->type = $request->type;
            $leave_type->total_days = $request->total_days;
            $result = $leave_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Type has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                return redirect('leave-type')->with('message-success', 'Type has been updated successfully');
            }

            return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

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
            $tables = \App\tableList::getTableList('type_id', $id);
            try {
                $leave_type = SmLeaveType::destroy($id);

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($leave_type) {
                        return ApiBaseMethod::sendResponse(null, 'Type has been deleted successfully');
                    }

                    return ApiBaseMethod::sendError('Something went wrong, please try again.');

                }

                if ($leave_type) {
                    return redirect()->back()->with('message-success-delete', 'Type has been deleted successfully');
                }

                return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
        }
    }
}
