<?php

namespace App\Http\Controllers\Admin\FeesCollection;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeesCollection\SmFeesTypeRequest;
use App\SmFeesGroup;
use App\SmFeesMaster;
use App\SmFeesPayment;
use App\SmFeesType;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmFeesTypeController extends Controller
{
    public function index(Request $request)
    {

        /*
        try {
        */
        $fees_types = SmFeesType::with('fessGroup')->where('school_id', Auth::user()->school_id)->get();
        $fees_groups = SmFeesGroup::where('school_id', Auth::user()->school_id)->get();

        return view('backEnd.feesCollection.fees_type', ['fees_types' => $fees_types, 'fees_groups' => $fees_groups]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmFeesTypeRequest $smFeesTypeRequest)
    {
        /*
        try {
        */
        $smFeesType = new SmFeesType();
        $smFeesType->name = $smFeesTypeRequest->name;
        $smFeesType->fees_group_id = $smFeesTypeRequest->fees_group;
        $smFeesType->description = $smFeesTypeRequest->description;
        $smFeesType->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $smFeesType->un_academic_id = getAcademicId();
        } else {
            $smFeesType->academic_id = getAcademicId();
        }

        $result = $smFeesType->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
        $fees_type = SmFeesType::where('school_id', Auth::user()->school_id)->find($id);
        $fees_types = SmFeesType::where('school_id', Auth::user()->school_id)->get();
        $fees_groups = SmFeesGroup::where('school_id', Auth::user()->school_id)->get();

        return view('backEnd.feesCollection.fees_type', ['fees_type' => $fees_type, 'fees_types' => $fees_types, 'fees_groups' => $fees_groups]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request)
    {

        // if ($validator->fails()) {
        //     if (ApiBaseMethod::checkUrl($request->fullUrl())) {
        //         return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        //     }

        // }

        /*
        try {
        */
        $fees_type = SmFeesType::find($request->id);
        $fees_type->name = $request->name;
        $fees_type->fees_group_id = $request->fees_group;
        $fees_type->description = $request->description;
        $result = $fees_type->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Fees type has been updated successfully.');
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again.');

        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('fees_type');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /* try { */
        $id_key = 'fees_type_id';

        $tables = tableList::getTableList($id_key, $id);

        /*
        try {
        */
        if ($tables == null) {
            $check_fees_type_in_master = SmFeesMaster::where('fees_type_id', $id)->first();
            $check_fees_type_in_payment = SmFeesPayment::where('active_status', 1)->where('fees_type_id', $id)->first();
            if ($check_fees_type_in_master !== null && $check_fees_type_in_payment !== null) {
                Toastr::warning('Operation Failed', 'Used Data');

                return redirect('fees-type');
            }

            $delete_query = SmFeesType::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($delete_query) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Type has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        }

        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();
    }

}
