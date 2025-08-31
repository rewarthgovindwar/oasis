<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SmCourseListRequest;
use App\SmCourse;
use App\SmCourseCategory;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmCourseListController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $course = SmCourse::where('school_id', app('school')->id)->get();
            $categories = SmCourseCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.course.course_page', ['course' => $course, 'categories' => $categories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmCourseListRequest $smCourseListRequest)
    {
/*
        try {
        */
            $destination = 'public/uploads/course/';
            $image = fileUpload($smCourseListRequest->image, $destination);

            $smCourse = new SmCourse();
            $smCourse->title = $smCourseListRequest->title;
            $smCourse->image = $image;
            $smCourse->category_id = $smCourseListRequest->category_id;
            $smCourse->overview = $smCourseListRequest->overview;
            $smCourse->outline = $smCourseListRequest->outline;
            $smCourse->prerequisites = $smCourseListRequest->prerequisites;
            $smCourse->resources = $smCourseListRequest->resources;
            $smCourse->stats = $smCourseListRequest->stats;
            $smCourse->school_id = app('school')->id;
            $result = $smCourse->save();

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
            $categories = SmCourseCategory::where('school_id', app('school')->id)->get();
            $course = SmCourse::where('school_id', app('school')->id)->get();
            $add_course = SmCourse::find($id);

            return view('backEnd.frontSettings.course.course_page', ['categories' => $categories, 'course' => $course, 'add_course' => $add_course]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmCourseListRequest $smCourseListRequest)
    {
/*
        try {
        */
            $destination = 'public/uploads/course/';

            $course = SmCourse::find($smCourseListRequest->id);
            $course->title = $smCourseListRequest->title;

            $course->image = fileUpdate($course->image, $smCourseListRequest->image, $destination);

            $course->category_id = $smCourseListRequest->category_id;
            $course->overview = $smCourseListRequest->overview;
            $course->outline = $smCourseListRequest->outline;
            $course->prerequisites = $smCourseListRequest->prerequisites;
            $course->resources = $smCourseListRequest->resources;
            $course->stats = $smCourseListRequest->stats;
            $course->school_id = app('school')->id;
            $result = $course->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('course-list');
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy($id)
    {
        /*
        try {
        */
            SmCourse::destroy($id);
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroyLms($id)
    {
        /*
        try {
        */
            \Modules\Lms\Entities\Course::destroy($id);
            if (moduleStatusCheck('OnlineExam')) {
                \Modules\OnlineExam\Entities\InfixOnlineExam::where('course_id', $id)->delete();
                \Modules\OnlineExam\Entities\InfixQuestionBank::where('course_id', $id)->delete();
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function forDeleteCourse($id)
    {
        /*
        try {
        */
            return view('backEnd.frontSettings.course.delete_modal', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function courseDetails($id)
    {
        /*
        try {
        */    $course = SmCourse::find($id);

            return view('backEnd.frontSettings.course.course_details', ['course' => $course]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
