<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\ItemListRequest;
use App\SmItem;
use App\SmItemCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmItemController extends Controller
{

    public function index(Request $request)
    {
        /* try { */
            $user = Auth::user();
            // $items = SmItem::with('category')->where('school_id',$user->school_id)->select([''])->get();
            $itemCategories = SmItemCategory::where('school_id', $user->school_id)->select(['category_name', 'id'])->get();

            return view('backEnd.inventory.itemList', ['itemCategories' => $itemCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(ItemListRequest $itemListRequest)
    {
        /*
        try {
        */
            $smItem = new SmItem();
            $smItem->item_name = $itemListRequest->item_name;
            $smItem->item_category_id = $itemListRequest->category_name;
            $smItem->total_in_stock = 0;
            $smItem->description = $itemListRequest->description;
            $smItem->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItem->un_academic_id = getAcademicId();
            } else {
                $smItem->academic_id = getAcademicId();
            }

            $smItem->save();

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
            if (checkAdmin() == true) {
                $editData = SmItem::find($id);
            } else {
                $editData = SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $items = SmItem::where('school_id', Auth::user()->school_id)->get();
            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.inventory.itemList', ['editData' => $editData, 'items' => $items, 'itemCategories' => $itemCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(ItemListRequest $itemListRequest, $id)
    {
        /*
        try {
        */
            if (checkAdmin() == true) {
                $items = SmItem::find($id);
            } else {
                $items = SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $items->item_name = $itemListRequest->item_name;
            $items->item_category_id = $itemListRequest->category_name;
            $items->description = $itemListRequest->description;
            if (moduleStatusCheck('University')) {
                $items->un_academic_id = getAcademicId();
            }

            $items->update();

            Toastr::success('Operation successful', 'Success');

            return redirect('item-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemView(Request $request, $id)
    {
        /*
        try {
        */
            $title = __('common.are_you_sure_to_detete_this_item');
            $url = route('delete-item', $id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItem(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('item_id', $id);
        /*
            try {
        */
                if ($tables == null) {
                    if (checkAdmin() == true) {
                        SmItem::destroy($id);
                    } else {
                        SmItem::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
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
