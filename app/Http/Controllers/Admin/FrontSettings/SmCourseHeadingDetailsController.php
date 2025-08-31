<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SmCourseHeadingDetailsRequest;
use App\SmCoursePage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmCourseHeadingDetailsController extends Controller
{


    //
    public function index()
    {

        /*
        try {
        */
            $SmCoursePage = SmCoursePage::where('is_parent', 0)->where('school_id', app('school')->id)->first();
            $update = '';

            return view('backEnd.frontSettings.course.courseDetailsHeading', ['SmCoursePage' => $SmCoursePage, 'update' => $update]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmCourseHeadingDetailsRequest $smCourseHeadingDetailsRequest)
    {

        /*try {*/
         

            $destination = 'public/uploads/about_page/';
            $course_heading = SmCoursePage::where('is_parent', 0)->where('school_id', app('school')->id)->first();
            if ($course_heading) {

                $course_heading->image = fileUpdate($course_heading->image, $smCourseHeadingDetailsRequest->image, $destination);

            } else {
                $course_heading = new SmCoursePage();
                $course_heading->image = fileUpload($smCourseHeadingDetailsRequest->image, $destination);
                $course_heading->school_id = app('school')->id;
                $course_heading->is_parent = 0;
            }

            $course_heading->title = $smCourseHeadingDetailsRequest->title;
            $course_heading->description = $smCourseHeadingDetailsRequest->description;
            $course_heading->main_title = $smCourseHeadingDetailsRequest->main_title;
            $course_heading->main_description = $smCourseHeadingDetailsRequest->main_description;
            $course_heading->button_text = $smCourseHeadingDetailsRequest->button_text;
            $course_heading->button_url = $smCourseHeadingDetailsRequest->button_url;
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
