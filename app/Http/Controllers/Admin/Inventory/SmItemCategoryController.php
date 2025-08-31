<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\ItemCategoryRequest;
use App\SmItemCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmItemCategoryController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $itemCategories = SmItemCategory::where('school_id', Auth::user()->school_id)->select(['id', 'category_name'])->get();

            return view('backEnd.inventory.itemCategoryList', ['itemCategories' => $itemCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(ItemCategoryRequest $itemCategoryRequest)
    {
        /*
        try {
        */
            $smItemCategory = new SmItemCategory();
            $smItemCategory->category_name = $itemCategoryRequest->category_name;
            $smItemCategory->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smItemCategory->un_academic_id = getAcademicId();
            } else {
                $smItemCategory->academic_id = getAcademicId();
            }

            $smItemCategory->save();

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
            $user = Auth::user();
            if (checkAdmin() == true) {
                $editData = SmItemCategory::find($id);
            } else {
                $editData = SmItemCategory::where('id', $id)->where('school_id', $user->school_id)->first();
            }

            $itemCategories = SmItemCategory::where('school_id', $user->school_id)->select(['id', 'category_name'])->get();

            return view('backEnd.inventory.itemCategoryList', ['itemCategories' => $itemCategories, 'editData' => $editData]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request, $id)
    {

        /*
        try {
        */
            if (checkAdmin() == true) {
                $categories = SmItemCategory::find($id);
            } else {
                $categories = SmItemCategory::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $categories->category_name = $request->category_name;
            if (moduleStatusCheck('University')) {
                $categories->un_academic_id = getAcademicId();
            }

            $categories->update();

            Toastr::success('Operation successful', 'Success');

            return redirect('item-category');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemCategoryView(Request $request, $id)
    {
        /*
        try {
        */
            $title = __('common.are_you_sure_to_detete_this_item');
            $url = route('delete-item-category', $id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteItemCategory(Request $request, $id)
    {
        $tables = \App\tableList::getTableList('item_category_id', $id);
        /*
        try {
        */
            if ($tables == null) {
                if (checkAdmin() == true) {
                    SmItemCategory::destroy($id);
                } else {
                    SmItemCategory::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $queryException) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        */
    }
}
