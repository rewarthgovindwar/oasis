<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmItem;
use App\SmItemCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmItemController extends Controller
{

    public function index(Request $request)
    {

        try {
            $items = SmItem::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();
            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['items'] = $items->toArray();
                $data['itemCategories'] = $itemCategories->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.inventory.itemList', ['items' => $items, 'itemCategories' => $itemCategories]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'item_name' => 'required',
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

        try {
            $smItem = new SmItem();
            $smItem->item_name = $request->item_name;
            $smItem->item_category_id = $request->category_name;
            $smItem->total_in_stock = 0;
            $smItem->description = $request->description;
            $smItem->school_id = Auth::user()->school_id;
            $smItem->academic_id = getAcademicId();
            $results = $smItem->save();

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
            // $editData = SmItem::find($id);
            if (checkAdmin() == true) {
                $editData = SmItem::find($id);
            } else {
                $editData = SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $items = SmItem::where('school_id', Auth::user()->school_id)->get();
            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['items'] = $items->toArray();
                $data['itemCategories'] = $itemCategories->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.inventory.itemList', ['editData' => $editData, 'items' => $items, 'itemCategories' => $itemCategories]);
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
            'item_name' => 'required',
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

        try {
            // $items = SmItem::find($id);
            if (checkAdmin() == true) {
                $items = SmItem::find($id);
            } else {
                $items = SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $items->item_name = $request->item_name;
            $items->item_category_id = $request->category_name;
            $items->description = $request->description;
            $results = $items->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Item has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect('item-list');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteItemView(Request $request, $id)
    {

        try {
            $title = 'Are you sure to detete this Item?';
            $url = route('delete-item', $id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteItem(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('item_id', $id);
            try {
                if ($tables == null) {
                    if (checkAdmin() == true) {
                        $result = SmItem::destroy($id);
                    } else {
                        $result = SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

                    if ($result) {

                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($result) {
                                return ApiBaseMethod::sendResponse(null, 'Item has been deleted successfully');
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

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
