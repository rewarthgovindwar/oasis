<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\NewsHeadingRequest;
use App\SmNewsPage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class NewsHeadingController extends Controller
{


    public function index()
    {

        /*
        try {
        */
            $SmNewsPage = SmNewsPage::where('school_id', app('school')->id)->first();
            $update = '';

            return view('backEnd.frontSettings.news.newsHeadingUpdate', ['SmNewsPage' => $SmNewsPage, 'update' => $update]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(NewsHeadingRequest $newsHeadingRequest)
    {
        /*
        try {
        */
            $path = 'public/uploads/about_page/';
            $newsHeading = SmNewsPage::where('school_id', app('school')->id)->first();

            if ($newsHeading) {
                $newsHeading->image = fileUpdate($newsHeading->image, $newsHeadingRequest->image, $path);
                $newsHeading->main_image = fileUpdate($newsHeading->main_image, $newsHeadingRequest->main_image, $path);
            } else {
                $newsHeading = new SmNewsPage();
                $newsHeading->image = fileUpload($newsHeadingRequest->image, $path);
                $newsHeading->main_image = fileUpload($newsHeadingRequest->main_image, $path);
                $newsHeading->school_id = app('school')->id;
            }

            $newsHeading->title = $newsHeadingRequest->title;
            $newsHeading->description = $newsHeadingRequest->description;
            $newsHeading->main_title = $newsHeadingRequest->main_title;
            $newsHeading->main_description = $newsHeadingRequest->main_description;
            $newsHeading->button_text = $newsHeadingRequest->button_text;
            $newsHeading->button_url = $newsHeadingRequest->button_url;
            $newsHeading->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('news-heading-update');
/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
