<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmUserLog;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function index()
    {
        try {
            return view('backEnd.systemSettings.user.user');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function create()
    {
        try {
            return view('backEnd.systemSettings.user.user_create');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function userLog(Request $request)
    {
        try {
            $user_logs = SmUserLog::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->orderBy('id', 'desc')
                ->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($user_logs, null);
            }

            return view('backEnd.reports.user_log', ['user_logs' => $user_logs]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
