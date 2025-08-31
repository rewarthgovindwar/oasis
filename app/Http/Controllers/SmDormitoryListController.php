<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmDormitoryList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmDormitoryListController extends Controller
{

    public function index(Request $request)
    {
        try {
            $dormitory_lists = SmDormitoryList::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($dormitory_lists, null);
            }

            return view('backEnd.dormitory.dormitory_list', ['dormitory_lists' => $dormitory_lists]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => 'required|max:200',
            'type' => 'required',
            'intake' => 'required',
        ]);

        // school wise uquine validation
        $is_duplicate = SmDormitoryList::where('school_id', Auth::user()->school_id)->where('dormitory_name', $request->dormitory_name)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate dormitory name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
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
            $smDormitoryList = new SmDormitoryList();
            $smDormitoryList->dormitory_name = $request->dormitory_name;
            $smDormitoryList->type = $request->type;
            $smDormitoryList->address = $request->address;
            $smDormitoryList->intake = $request->intake;
            $smDormitoryList->description = $request->description;
            $smDormitoryList->school_id = Auth::user()->school_id;
            $smDormitoryList->academic_id = getAcademicId();
            $result = $smDormitoryList->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Dormitory has been created successfully');
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

    public function show(Request $request, $id)
    {
        try {
            if (checkAdmin() == true) {
                $dormitory_list = SmDormitoryList::find($id);
            } else {
                $dormitory_list = SmDormitoryList::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $dormitory_lists = SmDormitoryList::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['dormitory_list'] = $dormitory_list;
                $data['dormitory_lists'] = $dormitory_lists->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.dormitory.dormitory_list', ['dormitory_lists' => $dormitory_lists, 'dormitory_list' => $dormitory_list]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function edit($id)
    {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'dormitory_name' => 'required|max:200',
            'type' => 'required',
            'intake' => 'required',
        ]);
        // school wise uquine validation
        $is_duplicate = SmDormitoryList::where('school_id', Auth::user()->school_id)->where('dormitory_name', $request->dormitory_name)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate dormitory name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
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
            if (checkAdmin() == true) {
                $dormitory_list = SmDormitoryList::find($request->id);
            } else {
                $dormitory_list = SmDormitoryList::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $dormitory_list->dormitory_name = $request->dormitory_name;
            $dormitory_list->type = $request->type;
            $dormitory_list->address = $request->address;
            $dormitory_list->intake = $request->intake;
            $dormitory_list->description = $request->description;
            $result = $dormitory_list->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Dormitory has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('dormitory-list');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('dormitory_id', $id);
            try {
                if ($tables == null) {
                    if (checkAdmin() == true) {
                        $dormitory_list = SmDormitoryList::destroy($id);
                    } else {
                        $dormitory_list = SmDormitoryList::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

                    if ($dormitory_list) {
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($dormitory_list) {
                                return ApiBaseMethod::sendResponse(null, 'Dormitory has been deleted successfully');
                            }

                            return ApiBaseMethod::sendError('Something went wrong, please try again');

                        }

                        if ($dormitory_list) {
                            Toastr::success('Operation successful', 'Success');

                            return redirect('dormitory-list');
                        }

                        Toastr::error('Operation Failed', 'Failed');

                        return redirect()->back();

                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();

            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
