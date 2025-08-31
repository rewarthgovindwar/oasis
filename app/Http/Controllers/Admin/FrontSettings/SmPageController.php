<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\ExamResultPageRequest;
use App\Http\Requests\Admin\FrontSettings\SmPageRequest;
use App\Models\FrontendExamResult;
use App\SmPage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmPageController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $pages = SmPage::where('school_id', app('school')->id)->where('is_dynamic', 1)->get();

            return view('backEnd.frontSettings.pageList', ['pages' => $pages]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function create()
    {
        return view('backEnd.frontSettings.createPage');
    }

    public function store(SmPageRequest $smPageRequest)
    {
        /*
        try {
        */

            $destination = 'public/uploads/pages/';
            $fileName = fileUpload($smPageRequest->file, $destination);
            $smPage = new SmPage();
            $smPage->title = $smPageRequest->title;
            $smPage->sub_title = $smPageRequest->sub_title;
            $smPage->slug = $smPageRequest->slug;
            $smPage->details = $smPageRequest->details;
            $smPage->header_image = $fileName;
            $smPage->school_id = app('school')->id;
            $smPage->save();
            Toastr::success('Operation successfull', 'Success');

            return redirect('create-page');
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
            $editData = SmPage::find($id);

            return view('backEnd.frontSettings.createPage', ['editData' => $editData]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmPageRequest $smPageRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/pages/';

            $data = SmPage::find($smPageRequest->id);
            $data->title = $smPageRequest->title;
            $data->sub_title = $smPageRequest->sub_title;
            $data->slug = $smPageRequest->slug;
            $data->details = $smPageRequest->details;
            $data->school_id = app('school')->id;
            $data->header_image = fileUpdate($data->header_image, $smPageRequest->file, $destination);
            $data->save();

            Toastr::success('Operation successfull', 'Success');

            return redirect('page-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examResultPage()
    {
        /*
        try {
        */
            $exam_page = FrontendExamResult::where('school_id', app('school')->id)->first();
            $update = '';

            return view('backEnd.frontSettings.examResultPage', ['exam_page' => $exam_page, 'update' => $update]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examResultPageUpdate(ExamResultPageRequest $examResultPageRequest)
    {
        /*
        try {
        */
            $path = 'public/uploads/about_page/';
            $newsHeading = FrontendExamResult::where('school_id', app('school')->id)->first();

            if ($newsHeading) {
                $newsHeading->image = fileUpdate($newsHeading->image, $examResultPageRequest->image, $path);
                $newsHeading->main_image = fileUpdate($newsHeading->main_image, $examResultPageRequest->main_image, $path);
            } else {
                $newsHeading = new FrontendExamResult();
                $newsHeading->image = fileUpload($examResultPageRequest->image, $path);
                $newsHeading->main_image = fileUpload($examResultPageRequest->main_image, $path);
                $newsHeading->school_id = app('school')->id;
            }

            $newsHeading->title = $examResultPageRequest->title;
            $newsHeading->description = $examResultPageRequest->description;
            $newsHeading->main_title = $examResultPageRequest->main_title;
            $newsHeading->main_description = $examResultPageRequest->main_description;
            $newsHeading->button_text = $examResultPageRequest->button_text;
            $newsHeading->button_url = $examResultPageRequest->button_url;
            $newsHeading->save();

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
