<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\AboutPageRequest;
use App\SmAboutPage;
use Brian2694\Toastr\Facades\Toastr;

class AboutPageController extends Controller
{
    public function index()
    {

        /*
        try {
        */
            $about_us = SmAboutPage::first();
            return view('backEnd.frontSettings.about_us', compact('about_us'));
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit()
    {

        /*
        try {
        */
            $about_us = SmAboutPage::first();
            $update = '';

            return view('backEnd.frontSettings.about_us', compact('about_us', 'update'));
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(AboutPageRequest $aboutPageRequest)
    {

        /*
        try {
        */

            $about = SmAboutPage::first();
            $destination = 'public/uploads/about_page/';

            if ($about) {

                $about->image = fileUpdate($about->image, $aboutPageRequest->image, $destination);
                $about->main_image = fileUpdate($about->main_image, $aboutPageRequest->main_image, $destination);
            } else {
                $about = new SmAboutPage();
                $about->image = fileUpload($aboutPageRequest->image, $destination);
                $about->main_image = fileUpload($aboutPageRequest->main_image, $destination);
                $about->school_id = app('school')->id;
            }

            $about->title = $aboutPageRequest->title;
            $about->description = $aboutPageRequest->description;
            $about->main_title = $aboutPageRequest->main_title;
            $about->main_description = $aboutPageRequest->main_description;
            $about->button_text = $aboutPageRequest->button_text;
            $about->button_url = $aboutPageRequest->button_url;
            $result = $about->save();

            Toastr::success('Operation Successful', 'Success');

            return redirect('about-page');
           
        /*
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
