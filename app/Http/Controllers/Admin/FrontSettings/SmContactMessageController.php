<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\SmContactMessage;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmContactMessageController extends Controller
{


    public function deleteMessage($id)
    {
        /*
        try {
        */
            SmContactMessage::find($id)->delete();
            Toastr::success('Operation successful', 'Success');

            return redirect('contact-message');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
