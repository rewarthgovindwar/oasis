<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Library\BooksCategoryRequest;
use App\SmBookCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmBookCategoryController extends Controller
{

    public function index()
    {
        /*
        try {
        */
            $bookCategories = SmBookCategory::status()->get();

            return view('backEnd.library.bookCategoryList', ['bookCategories' => $bookCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function create(): void
    {
        //
    }

    public function store(BooksCategoryRequest $booksCategoryRequest)
    {
        /*
        try {
        */
            $smBookCategory = new SmBookCategory();
            $smBookCategory->category_name = $booksCategoryRequest->category_name;
            $smBookCategory->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smBookCategory->un_academic_id = getAcademicId();
            } else {
                $smBookCategory->academic_id = getAcademicId();
            }

            $smBookCategory->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('book-category-list');

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
            // $editData = SmBookCategory::find($id);
            $editData = SmBookCategory::status()->find($id);
            $bookCategories = SmBookCategory::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.library.bookCategoryList', ['bookCategories' => $bookCategories, 'editData' => $editData]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(BooksCategoryRequest $booksCategoryRequest, $id)
    {
/*
        try {*/
            $categories = SmBookCategory::find($id);
            $categories->category_name = $booksCategoryRequest->category_name;
            $categories->update();
            Toastr::success('Operation successful', 'Success');

            return redirect('book-category-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function destroy($id)
    {

        $tables = \App\tableList::getTableList('book_category_id', $id);
        $tables1 = \App\tableList::getTableList('sb_category_id', $id);
        /*
        try {
        */
            if ($tables == null && $tables1 == null) {
                SmBookCategory::status()->find($id)->delete();
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            $msg = 'This data already used in  : '.$tables.$tables1.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $queryException) {

            $msg = 'This data already used in  : '.$tables.$tables1.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        */

    }

    public function deleteBookCategoryView(Request $request, string $id)
    {
        /*
        try {
        */
            $title = 'Are you sure to detete this Book category?';
            $url = url('delete-book-category/'.$id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function deleteBookCategory($id)
    {

        $tables = \App\tableList::getTableList('book_category_id', $id);
        /*
        try {
        */
            if ($tables == null) {
                SmBookCategory::status()->find($id)->delete();
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
