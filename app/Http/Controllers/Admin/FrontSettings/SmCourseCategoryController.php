<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use Exception;
use App\SmCourse;
use App\SmCourseCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\FrontSettings\SmCourseCategoryRequest;

class SmCourseCategoryController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $course_categories = SmCourseCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.course.course_category', ['course_categories' => $course_categories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmCourseCategoryRequest $smCourseCategoryRequest)
    {
/*
        try {
*/

            $destination = 'public/uploads/course/';
            $image = fileUpload($smCourseCategoryRequest->category_image, $destination);

            SmCourseCategory::create([
                'category_name' => $smCourseCategoryRequest->category_name,
                'category_image' => $image,
                'school_id' => app('school')->id,
            ]);

            Toastr::success('Operation Successfull', 'Success');

            return redirect('course-category');
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
            $editData = SmCourseCategory::where('id', $id)
                ->where('school_id', app('school')->id)
                ->first();

            $course_categories = SmCourseCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.course.course_category', ['editData' => $editData, 'course_categories' => $course_categories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmCourseCategoryRequest $smCourseCategoryRequest)
    {
/*
        try {
*/
            
            $destination = 'public/uploads/course/';

            $data = SmCourseCategory::find($smCourseCategoryRequest->id);
            $data->category_name = $smCourseCategoryRequest->category_name;
            $data->school_id = app('school')->id;

            $data->category_image = fileUpdate($data->category_image, $smCourseCategoryRequest->category_image, $destination);

            $result = $data->save();

            Toastr::success('Operation Successfull', 'Success');

            return redirect('course-category');
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
            $tables = SmCourse::where('category_id', 1)->first();
            if ($tables == null) {
                $data = SmCourseCategory::find($request->id);
                if ($data->category_image !== '') {
                    unlink($data->category_image);
                }

                $data->delete();
            } else {
                $msg = 'This category is already assigned with a course.';
                Toastr::warning($msg, 'Warning');

                return redirect()->back();
            }

            Toastr::success('Operation Successfull', 'Success');

            return redirect('course-category');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function view($id)
    {
        /*
        try {
        */
            $category_id = SmCourseCategory::find($id);
            $courseCtaegories = SmCourse::where('category_id', $category_id->id)
                ->where('school_id', app('school')->id)
                ->get();

            return view('frontEnd.home.course_category', ['category_id' => $category_id, 'courseCtaegories' => $courseCtaegories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
