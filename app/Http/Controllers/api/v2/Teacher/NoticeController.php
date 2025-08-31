<?php

namespace App\Http\Controllers\api\v2\Teacher;

use App\SmNoticeBoard;
use App\Http\Controllers\Controller;

class NoticeController extends Controller
{
    public function noticeList()
    {
        auth()->user()->roles;

        $data = SmNoticeBoard::where('inform_to', 'like', '%"4"%')
            ->where('school_id', auth()->user()->school_id)
            ->where('publish_on', '<=', date('Y-m-d'))
            ->orderBy('id', 'DESC')
            ->get(['id', 'notice_title', 'notice_message', 'notice_date']);

        if (! $data) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Notice list',
            ];
        }

        return response()->json($response);
    }
}
