<?php

namespace App\Http\Controllers\api\v2\PushNotification;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function setFcmToken(Request $request)
    {
        $user = User::where('school_id', auth()->user()->school_id)
            ->where('id', $request->id)->first();
        $user->device_token = $request->token;
        $user->save();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = '';

            return ApiBaseMethod::sendResponse($data, 'Token Updated');
        }

        return null;
    }
}
