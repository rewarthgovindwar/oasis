<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Scopes\AcademicSchoolScope;
use App\SmAcademicYear;
use App\SmFeesGroup;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiSmFeesGroupController extends Controller
{
    public function fees_group_index(Request $request)
    {

        try {
            $fees_groups = SmFeesGroup::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_groups, null);
            }

            return view('backEnd.feesCollection.fees_group', ['fees_groups' => $fees_groups]);
        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_fees_group_index(Request $request, $school_id)
    {

        try {
            $fees_groups = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($fees_groups, null);
            }

            return view('backEnd.feesCollection.fees_group', ['fees_groups' => $fees_groups]);
        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function fees_group_store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|unique:sm_fees_groups|max:200',
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
            $smFeesGroup = new SmFeesGroup();
            $smFeesGroup->name = $request->name;
            $smFeesGroup->description = $request->description;
            $result = $smFeesGroup->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been created successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_fees_group_store(Request $request, $school_id)
    {
        try {
            $request->validate([
                'name' => 'required|unique:sm_fees_groups,name|max:200',
            ]);

            $smFeesGroup = new SmFeesGroup();
            $smFeesGroup->name = $request->name;
            $smFeesGroup->description = $request->description;
            $smFeesGroup->school_id = $school_id;
            $smFeesGroup->academic_id = SmAcademicYear::API_ACADEMIC_YEAR($school_id);
            $result = $smFeesGroup->save();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Fees Group has been created successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }

    public function fees_group_edit(Request $request, $id)
    {

        try {
            $fees_group = SmFeesGroup::find($id);
            $fees_groups = SmFeesGroup::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_group'] = $fees_group->toArray();
                $data['fees_groups'] = $fees_groups->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_group', ['fees_group' => $fees_group, 'fees_groups' => $fees_groups]);
        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_fees_group_edit(Request $request, $school_id, $id)
    {

        try {
            $fees_group = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->find($id);
            $fees_groups = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees_group'] = $fees_group->toArray();
                $data['fees_groups'] = $fees_groups->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.feesCollection.fees_group', ['fees_group' => $fees_group, 'fees_groups' => $fees_groups]);
        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function fees_group_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|max:200|unique:sm_fees_groups,name,'.$request->id_,
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
            $visitor = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->find($request->id_);

            if (! $visitor) {
                return ApiBaseMethod::sendError('Fees Group not found.');
            }

            $visitor->name = $request->name;
            $visitor->description = $request->description;

            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been updated successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('fees-group');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }

    }

    public function saas_fees_group_update(Request $request, $school_id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|max:200',

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
            $visitor = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $request->school_id)->find($request->id);
            $visitor->name = $request->name;
            $visitor->description = $request->description;
            $visitor->school_id = $school_id;
            $result = $visitor->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been updated successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('fees-group');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function fees_group_delete(Request $request)
    {

        try {
            $fees_group = SmFeesGroup::destroy($request->id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($fees_group) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been deleted successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($fees_group) {
                Toastr::success('Operation successful', 'Success');

                return redirect('fees-group');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect('fees-group');

        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_fees_group_delete(Request $request, $school_id)
    {

        try {
            $fees_group = SmFeesGroup::withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', $school_id)->where('id', $request->id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($fees_group) {
                    return ApiBaseMethod::sendResponse(null, 'Fees Group has been deleted successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($fees_group) {
                Toastr::success('Operation successful', 'Success');

                return redirect('fees-group');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect('fees-group');

        } catch (Exception$exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }
}
