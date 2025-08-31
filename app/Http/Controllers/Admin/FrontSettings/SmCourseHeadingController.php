<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SmCourseHeadingRequest;
use App\SmCoursePage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmCourseHeadingController extends Controller
{


    public function index()
    {

        /*
        try {
        */
            $SmCoursePage = SmCoursePage::where('school_id', app('school')->id)->first();
            $update = '';

            return view('backEnd.frontSettings.course.courseHeadingUpdate', ['SmCoursePage' => $SmCoursePage, 'update' => $update]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmCourseHeadingRequest $smCourseHeadingRequest)
    {

        /*try {*/
         

            $destination = 'public/uploads/about_page/';
            $course_heading = SmCoursePage::where('school_id', app('school')->id)->first();
            if ($course_heading) {

                $course_heading->image = fileUpdate($course_heading->image, $smCourseHeadingRequest->image, $destination);

            } else {
                $course_heading = new SmCoursePage();
                $course_heading->image = fileUpload($smCourseHeadingRequest->image, $destination);
                $course_heading->school_id = app('school')->id;
            }

            $course_heading->title = $smCourseHeadingRequest->title;
            $course_heading->description = $smCourseHeadingRequest->description;
            $course_heading->main_title = $smCourseHeadingRequest->main_title;
            $course_heading->main_description = $smCourseHeadingRequest->main_description;
            $course_heading->button_text = $smCourseHeadingRequest->button_text;
            $course_heading->button_url = $smCourseHeadingRequest->button_url;
            $course_heading->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('course-heading-update');
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
