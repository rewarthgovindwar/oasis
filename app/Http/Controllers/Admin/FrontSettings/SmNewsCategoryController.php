<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SmNewsCategorRequest;
use App\SmNewsCategory;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class SmNewsCategoryController extends Controller
{


    public function index()
    {

        /*
        try {
        */
            $newsCategories = SmNewsCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.news.news_category', ['newsCategories' => $newsCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmNewsCategorRequest $smNewsCategorRequest)
    {
/*
        try {
        */
            $smNewsCategory = new SmNewsCategory();
            $smNewsCategory->category_name = $smNewsCategorRequest->category_name;
            $smNewsCategory->school_id = app('school')->id;
            $smNewsCategory->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
            $newsCategories = SmNewsCategory::where('school_id', app('school')->id)->get();
            $editData = SmNewsCategory::find($id);

            return view('backEnd.frontSettings.news.news_category', ['newsCategories' => $newsCategories, 'editData' => $editData]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmNewsCategorRequest $smNewsCategorRequest)
    {

        /*
        try {
        */
            $news_category = SmNewsCategory::find($smNewsCategorRequest->id);
            $news_category->category_name = $smNewsCategorRequest->category_name;
            $news_category->school_id = app('school')->id;
            $news_category->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('news-category');
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteModalOpen($id)
    {

        /*
        try {
        */
            return view('backEnd.frontSettings.news.category_delete_modal', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /*
        try {
        */
            $fk_id = 'category_id';

            $tables = \App\tableList::getTableList($fk_id, $id);

            /*
        try {
        */
                $delete_query = SmNewsCategory::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'News Category has been deleted successfully');
                    }
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');

                }

                if ($delete_query) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }

        /*
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
            Toastr::error('This item already used', 'Failed');
            return redirect()->back();
           }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
