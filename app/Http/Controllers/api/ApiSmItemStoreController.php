<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmItemStore;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmItemStoreController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $itemstores = SmItemStore::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($itemstores, null);
            }

            return view('backEnd.inventory.itemStoreList', ['itemstores' => $itemstores]);
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
            'store_name' => 'required|max:120',
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
            $smItemStore = new SmItemStore();
            $smItemStore->store_name = $request->store_name;
            $smItemStore->store_no = $request->store_no;
            $smItemStore->description = $request->description;
            $results = $smItemStore->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Store has been added successfully');
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {

        try {
            $editData = SmItemStore::find($id);
            $itemstores = SmItemStore::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['itemstores'] = $itemstores->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.inventory.itemStoreList', ['editData' => $editData, 'itemstores' => $itemstores]);
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
            'store_name' => 'required|max:120',
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
            $stores = SmItemStore::find($id);
            $stores->store_name = $request->store_name;
            $stores->store_no = $request->store_no;
            $stores->description = $request->description;
            $results = $stores->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Store has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect('item-store');
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
     */
    public function destroy($id): void
    {
        //
    }

    public function deleteStoreView(Request $request, string $id)
    {
        try {
            $title = 'Are you sure to detete this Item store?';
            $url = url('delete-store/'.$id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteStore(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('store_id', $id);
            try {
                $result = SmItemStore::destroy($id);
                if ($result) {

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($result) {
                            return ApiBaseMethod::sendResponse(null, 'Store has been deleted successfully');
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

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
