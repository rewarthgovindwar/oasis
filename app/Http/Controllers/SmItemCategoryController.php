<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmItemCategory;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmItemCategoryController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($itemCategories, null);
            }

            return view('backEnd.inventory.itemCategoryList', ['itemCategories' => $itemCategories]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category_name' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // school wise uquine validation
        $is_duplicate = SmItemCategory::where('school_id', Auth::user()->school_id)->where('category_name', $request->category_name)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $smItemCategory = new SmItemCategory();
            $smItemCategory->category_name = $request->category_name;
            $smItemCategory->school_id = Auth::user()->school_id;
            $smItemCategory->academic_id = getAcademicId();
            $results = $smItemCategory->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New Category has been added successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($results) {
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

    public function edit(Request $request, $id)
    {

        try {
            // $editData = SmItemCategory::find($id);
            if (checkAdmin() == true) {
                $editData = SmItemCategory::find($id);
            } else {
                $editData = SmItemCategory::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['itemCategories'] = $itemCategories->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.inventory.itemCategoryList', ['itemCategories' => $itemCategories, 'editData' => $editData]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
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
        $validator = Validator::make($input, [
            'category_name' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // school wise uquine validation
        $is_duplicate = SmItemCategory::where('school_id', Auth::user()->school_id)->where('category_name', $request->category_name)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // $categories = SmItemCategory::find($id);
            if (checkAdmin() == true) {
                $categories = SmItemCategory::find($id);
            } else {
                $categories = SmItemCategory::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $categories->category_name = $request->category_name;
            $results = $categories->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Category has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect('item-category');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteItemCategoryView(Request $request, $id)
    {
        try {
            $title = __('common.are_you_sure_to_detete_this_item');
            $url = route('delete-item-category', $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteItemCategory(Request $request, $id)
    {

        $tables = \App\tableList::getTableList('item_category_id', $id);

        try {
            if ($tables == null) {
                if (checkAdmin() == true) {
                    $result = SmItemCategory::destroy($id);
                } else {
                    $result = SmItemCategory::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                }

                if ($result) {
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Item Category has been deleted successfully');
                        }

                        return ApiBaseMethod::sendError('Something went wrong, please try again.');

                    }

                    if ($result) {
                        Toastr::success('Operation successful', 'Success');

                        return redirect()->back();
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

        } catch (\Illuminate\Database\QueryException $queryException) {

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }

    }
}
