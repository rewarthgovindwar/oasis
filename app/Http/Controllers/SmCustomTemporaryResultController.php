<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmCustomTemporaryResultController extends Controller
{
    public function index()
    {
        try {
            return redirect('login');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
