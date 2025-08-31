<?php

namespace App\Http\Controllers\Admin\Inventory;

use Exception;
use App\SmItemStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Inventory\ItemStoreRequest;

class SmItemStoreController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $itemstores = SmItemStore::where('school_id', Auth::user()->school_id)
                ->select(['store_name', 'id', 'store_no', 'description'])
                ->get();

            return view('backEnd.inventory.itemStoreList', ['itemstores' => $itemstores]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(ItemStoreRequest $itemStoreRequest)
    {
        /*
        try {
        */
            $smItemStore = new SmItemStore();
            $smItemStore->store_name = $itemStoreRequest->store_name;
            $smItemStore->store_no = $itemStoreRequest->store_no;
            $smItemStore->description = $itemStoreRequest->description;
            $smItemStore->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItemStore->un_academic_id = getAcademicId();
            } else {
                $smItemStore->academic_id = getAcademicId();
            }

            $smItemStore->save();

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
            $editData = SmItemStore::find($id);
            $itemstores = SmItemStore::where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.inventory.itemStoreList', ['editData' => $editData, 'itemstores' => $itemstores]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(ItemStoreRequest $itemStoreRequest, $id)
    {
        /*
        try {
        */
            $stores = SmItemStore::find($id);
            $stores->store_name = $itemStoreRequest->store_name;
            $stores->store_no = $itemStoreRequest->store_no;
            $stores->description = $itemStoreRequest->description;
            if (moduleStatusCheck('University')) {
                $stores->un_academic_id = getAcademicId();
            }
            $stores->update();
            Toastr::success('Operation successful', 'Success');
            return redirect('item-store');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteStoreView(Request $request, $id)
    {
        /*
        try {
        */
            $title = __('inventory.delete_store');
            $url = route('delete-store', $id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteStore(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('store_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    SmItemStore::destroy($id);
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                }
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
        /*
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
    } catch (Exception $exception) {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }
    */
    }
}
