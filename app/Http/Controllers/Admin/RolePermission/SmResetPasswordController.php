<?php

namespace App\Http\Controllers\Admin\RolePermission;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SmResetPasswordController extends Controller
{
    public function resetStudentPassword(Request $request)
    {
        /*
        try {
        */
        if ($request->new_password == '') {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('New Password and id field are required');
            }

            return redirect()->with('message-dander', 'New Password field is required');
        }

        $password = Hash::make($request->new_password);
        $user = User::find($request->id);
        $user->password = $password;
        $result = $user->save();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Password reset has been successfully');
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again');

        }
        if ($result) {
            Toastr::success('Password reset has been successfully', 'Success');

            return redirect()->back();
        }
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();

        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
