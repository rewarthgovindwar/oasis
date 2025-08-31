<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\HomePageRequest;
use App\SmFrontendPersmission;
use App\SmHomePageSetting;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Auth;

class HomePageController extends Controller
{
    public function index()
    {
        /*
        try {
        */
            $links = SmHomePageSetting::where('school_id', app('school')->id)->first();
            $permisions = SmFrontendPersmission::where('parent_id', 1)->get();
            return view('backEnd.frontSettings.homePageBackend', compact('links', 'permisions'));
        /*
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(HomePageRequest $homePageRequest)
    {

        /*
        try {
        */
            $permisionsArray = $homePageRequest->permisions ?? [];
            SmFrontendPersmission::where('parent_id', 1)->update(['is_published' => 0]);
            foreach ($permisionsArray as $permisionArray) {
                SmFrontendPersmission::where('id', $permisionArray)->update(['is_published' => 1]);
            }

            $path = 'public/uploads/homepageCreate/';

            // Update Home Page
            $update = SmHomePageSetting::where('school_id', app('school')->id)->first();
            $update->title = $homePageRequest->title;
            $update->long_title = $homePageRequest->long_title;
            $update->short_description = $homePageRequest->short_description;
            $update->link_label = $homePageRequest->link_label;
            $update->link_url = $homePageRequest->link_url;
            $update->school_id = app('school')->id;
            $update->image = fileUpdate($update->image, $homePageRequest->image, $path);
            $update->save();

            Toastr::success('Operation Successful', 'Success');

            return redirect()->route('admin-home-page');
           
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
