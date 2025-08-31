<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Models\SmClassExamRoutinePage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class SmClassExamRoutinePageController extends Controller
{


    public function classExamRoutinePage()
    {
        /*
        try {
        */
            $routine_page = SmClassExamRoutinePage::where('school_id', app('school')->id)->first();
            $update = '';

            return view('backEnd.frontSettings.classExamRoutinePage', ['routine_page' => $routine_page, 'update' => $update]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function classExamRoutinePageUpdate(Request $request)
    {
        /*
        try {
        */
            $path = 'public/uploads/about_page/';
            $routinePage = SmClassExamRoutinePage::where('school_id', app('school')->id)->first();

            if ($routinePage) {
                $routinePage->image = fileUpdate($routinePage->image, $request->image, $path);
                $routinePage->main_image = fileUpdate($routinePage->main_image, $request->main_image, $path);
            } else {
                $routinePage = new SmClassExamRoutinePage();
                $routinePage->image = fileUpload($request->image, $path);
                $routinePage->main_image = fileUpload($request->main_image, $path);
                $routinePage->school_id = app('school')->id;
            }

            $routinePage->title = $request->title;
            $routinePage->description = $request->description;
            $routinePage->main_title = $request->main_title;
            $routinePage->main_description = $request->main_description;
            $routinePage->button_text = $request->button_text;
            $routinePage->button_url = $request->button_url;
            $routinePage->class_routine = $request->class_routine;
            $routinePage->exam_routine = $request->exam_routine;
            $routinePage->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
