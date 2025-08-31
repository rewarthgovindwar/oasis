<?php

namespace App\Http\Controllers\Admin\Style;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Style\SmBackGroundSettingRequest;
use App\SmBackgroundSetting;
use App\SmStyle;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Auth;

class SmBackGroundSettingController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $background_settings = SmBackgroundSetting::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();

            return view('backEnd.style.background_setting', ['background_settings' => $background_settings]);
        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmBackGroundSettingRequest $smBackGroundSettingRequest)
    {

        /*
        try {
        */

            $destination = 'public/uploads/backgroundImage/';
            $fileName = fileUpload($smBackGroundSettingRequest->image, $destination);
            // changes for lead module
            if ($smBackGroundSettingRequest->style == 1) {
                $title = 'Dashboard Background';
            } elseif ($smBackGroundSettingRequest->style == 2) {
                $title = 'Login Background';
            } elseif ($smBackGroundSettingRequest->style == 3) {
                $title = 'Lead Form Background';
            }

            // end

            $smBackgroundSetting = new SmBackgroundSetting();
            $smBackgroundSetting->is_default = 0;
            $smBackgroundSetting->title = $title;
            $smBackgroundSetting->type = $smBackGroundSettingRequest->background_type;
            $smBackgroundSetting->school_id = Auth::user()->school_id;
            if ($smBackGroundSettingRequest->background_type == 'color') {
                $smBackgroundSetting->color = $smBackGroundSettingRequest->color;
            } else {
                $smBackgroundSetting->image = $fileName;
            }

            $smBackgroundSetting->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function status($id)
    {
        /*
        try {
        */
            $background = SmBackgroundSetting::find($id);
            if ($background->is_default == 1 && $background->title == 'Login Background') {
                SmBackgroundSetting::where([['is_default', 1], ['title', 'Login Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
            } elseif ($background->is_default == 1 && $background->title == 'Dashboard Background') {
                SmBackgroundSetting::where([['is_default', 1], ['title', 'Dashboard Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
            } elseif ($background->is_default == 0 && $background->title == 'Login Background') {
                SmBackgroundSetting::where([['is_default', 1], ['title', 'Login Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
            } elseif ($background->is_default == 0 && $background->title == 'Dashboard Background') {
                SmBackgroundSetting::where([['is_default', 1], ['title', 'Dashboard Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
            }

            // changes for lead form background -abunayem
            if (moduleStatusCheck('Lead') == true) {
                if ($background->is_default == 1 && $background->title == 'Lead Form Background') {
                    SmBackgroundSetting::where([['is_default', 1], ['title', 'Lead Form Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                    $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
                } elseif ($background->is_default == 0 && $background->title == 'Lead Form Background') {
                    SmBackgroundSetting::where([['is_default', 1], ['title', 'Lead Form Background']])->where('school_id', Auth::user()->school_id)->update(['is_default' => 0]);
                    $result = SmBackgroundSetting::where('id', $id)->update(['is_default' => 1]);
                }
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmBackGroundSettingRequest $smBackGroundSettingRequest)
    {

        /*
        try {
        */

            $destination = 'public/uploads/backgroundImage/';

            $background_setting = SmBackgroundSetting::find(1);
            $background_setting->type = $smBackGroundSettingRequest->type;
            if ($smBackGroundSettingRequest->type == 'color') {
                $background_setting->color = $smBackGroundSettingRequest->color;
                $background_setting->image = '';
                if ($background_setting->image !== '' && file_exists($background_setting->image)) {
                    unlink($background_setting->image);
                }
            } else {
                $background_setting->color = '';
                $background_setting->image = fileUpload($background_setting->image, $smBackGroundSettingRequest->image);
            }

            $background_setting->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        /*
        } catch (Exception$exception) {
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
            $delBGS = SmBackgroundSetting::where('id', $id)->where('is_default', 1)->first();
            if (empty($delBGS)) {
                SmBackgroundSetting::find($id)->delete();

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();

            }

            Toastr::warning('You cannot delete default Background', 'Warning');

            return redirect()->back();

        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function colorTheme()
    {
        /*
        try {
        */
            $color_styles = SmStyle::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.systemSettings.color_theme', ['color_styles' => $color_styles]);
        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function colorThemeSet($id)
    {
        /*
        try {
        */

            SmStyle::where('is_active', 1)->where('school_id', Auth::user()->school_id)->update(['is_active' => 0]);
            $result = SmStyle::where('id', $id)->update(['is_active' => 1]);
            if ($result) {
                session()->forget('all_styles');
                $all_styles = SmStyle::where('school_id', 1)->where('active_status', 1)->get();
                session()->put('all_styles', $all_styles);

                session()->forget('active_style');
                $active_style = SmStyle::where('school_id', 1)->where('is_active', 1)->first();
                session()->put('active_style', $active_style);

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception$exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
