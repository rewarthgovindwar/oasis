<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\SmCustomLink;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class SmFooterWidgetController extends Controller
{


    public function index()
    {

        /*
        try {
        */
            $links = SmCustomLink::where('school_id', app('school')->id)->first();

            return view('backEnd.systemSettings.customLinks', ['links' => $links]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request)
    {
        /*
        try {
        */
            $links = SmCustomLink::where('school_id', app('school')->id)->first();
            if (! $links) {
                $links = new SmCustomLink();
                $links->school_id = app('school')->id;
            }

            $lists = ['title1', 'link_label1', 'link_href1', 'link_label2', 'link_href2', 'link_label3', 'link_href3', 'link_label4', 'title2', 'link_href4', 'link_label5', 'link_href5', 'link_label6', 'link_href6', 'link_label7', 'link_href7', 'link_label8', 'link_href8', 'title3', 'link_label9', 'link_href9', 'link_label10', 'link_href10', 'link_label11', 'link_href11', 'link_label12', 'link_href12', 'title4', 'link_label13', 'link_href13', 'link_label14', 'link_href14', 'link_label15', 'link_href15', 'link_label16', 'link_href16'];

            foreach ($lists as $list) {
                $links->$list = $request->$list;
            }

            $result = $links->save();

            $links->save();

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
