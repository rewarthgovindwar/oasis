<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SocialMediaRequest;
use App\SmSocialMediaIcon;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmSocialMediaController extends Controller
{
    public function index()
    {
        $visitors = SmSocialMediaIcon::where('school_id', app('school')->id)->get();

        return view('backEnd.frontSettings.socialMedia', ['visitors' => $visitors]);
    }

    public function store(SocialMediaRequest $socialMediaRequest)
    {
        /*
        try {
*/

        $smSocialMediaIcon = new SmSocialMediaIcon();
        $smSocialMediaIcon->url = $socialMediaRequest->url;
        $smSocialMediaIcon->icon = $socialMediaRequest->icon;
        $smSocialMediaIcon->status = $socialMediaRequest->status;
        $smSocialMediaIcon->school_id = app('school')->id;
        $result = $smSocialMediaIcon->save();

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

        $visitors = SmSocialMediaIcon::where('school_id', app('school')->id)->get();
        $visitor = SmSocialMediaIcon::where('school_id', app('school')->id)->findOrFail($id);

        return view('backEnd.frontSettings.socialMedia', ['visitors' => $visitors, 'visitor' => $visitor]);

        /*
        } catch (\Exception $e) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
*/

    }

    public function update(SocialMediaRequest $socialMediaRequest)
    {
        /*
        try {
*/

        $visitor = SmSocialMediaIcon::where('school_id', app('school')->id)->findOrFail($socialMediaRequest->id);
        $visitor->url = $socialMediaRequest->url;
        $visitor->icon = $socialMediaRequest->icon;
        $visitor->status = $socialMediaRequest->status;
        $result = $visitor->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('social-media');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete($id)
    {
        /*
        try {
        */

        SmSocialMediaIcon::destroy($id);

        Toastr::success('Operation successful', 'Success');

        return redirect('social-media');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
