<?php

namespace App\Http\Controllers\Admin\Communicate;

use App\GlobalVariable;
use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeRequestForm;
use App\SmNoticeBoard;
use App\SmNotification;
use App\Traits\NotificationSend;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\RolePermission\Entities\InfixRole;
use Modules\Saas\Entities\SmAdministratorNotice;

class SmNoticeController extends Controller
{
    use NotificationSend;

    public function sendMessage(Request $request)
    {
        /*
        try {
        */
            $roles = InfixRole::where('is_saas', 0)->when((generalSetting()->with_guardian !== 1), function ($query): void {
                $query->where('id', '!=', 3);
            })->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

            return view('backEnd.communicate.sendMessage', ['roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveNoticeData(NoticeRequestForm $noticeRequestForm)
    {
        /*
        try {
        */
            $smNoticeBoard = new SmNoticeBoard();
            if (property_exists($noticeRequestForm, 'is_published') && $noticeRequestForm->is_published !== null) {
                $smNoticeBoard->is_published = $noticeRequestForm->is_published;
            }

            $smNoticeBoard->notice_title = $noticeRequestForm->notice_title;
            $smNoticeBoard->notice_message = $noticeRequestForm->notice_message;
            $smNoticeBoard->notice_date = date('Y-m-d', strtotime($noticeRequestForm->notice_date));
            $smNoticeBoard->publish_on = date('Y-m-d', strtotime($noticeRequestForm->publish_on));
            $smNoticeBoard->inform_to = json_encode($noticeRequestForm->role);
            $smNoticeBoard->created_by = Auth::user()->id;
            $smNoticeBoard->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smNoticeBoard->un_academic_id = getAcademicId();
            } else {
                $smNoticeBoard->academic_id = getAcademicId();
            }

            $smNoticeBoard->save();

            $data['title'] = $noticeRequestForm->notice_title;
            $data['notice'] = $noticeRequestForm->notice_title;
            foreach ($noticeRequestForm->role as $role_id) {
                $userIds = User::where('role_id', $role_id)->where('active_status', 1)->pluck('id')->toArray();
                if ($role_id == 4) {
                    $this->sent_notifications('Notice', $userIds, $data, ['Teacher']);
                } elseif ($role_id == 2) {
                    $this->sent_notifications('Notice', $userIds, $data, ['Student']);
                } elseif ($role_id == 3) {
                    $this->sent_notifications('Notice', $userIds, $data, ['Parent']);
                } elseif ($role_id == GlobalVariable::isAlumni()) {
                    $this->sent_notifications('Notice', $userIds, $data, ['Alumni']);
                }
            }

            if ($noticeRequestForm->role !== null) {
                foreach ($noticeRequestForm->role as $role) {
                    $users = User::where('role_id', $role)->where('active_status', 1)->get();
                    foreach ($users as $user) {
                        $notification = new SmNotification();
                        $notification->role_id = $role;
                        $notification->message = 'Notice for you';
                        $notification->date = $smNoticeBoard->notice_date;
                        $notification->user_id = $user->id;
                        $notification->url = 'notice-list';
                        $notification->school_id = Auth::user()->school_id;
                        if (moduleStatusCheck('University')) {
                            $notification->un_academic_id = getAcademicId();
                        } else {
                            $notification->academic_id = getAcademicId();
                        }

                        $notification->save();
                    }
                }
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('notice-list');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function noticeList(Request $request)
    {
        /*
        try {
        */
            $allNotices = SmNoticeBoard::with('users')
                ->where('publish_on', '<=', date('Y-m-d'))
                ->orderBy('id', 'DESC')
                ->get();

            return view('backEnd.communicate.noticeList', ['allNotices' => $allNotices]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function administratorNotice(Request $request)
    {
        /*
        try {
        */
            $allNotices = SmAdministratorNotice::where('inform_to', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();

            return view('backEnd.communicate.administratorNotice', ['allNotices' => $allNotices]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function editNotice(Request $request, $notice_id)
    {

        /*
        try {
        */
            $roles = InfixRole::where('is_saas', 0)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
            $noticeDataDetails = SmNoticeBoard::find($notice_id);

            return view('backEnd.communicate.editSendMessage', ['noticeDataDetails' => $noticeDataDetails, 'roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateNoticeData(NoticeRequestForm $noticeRequestForm)
    {
        /*
        try {
*/

            $noticeData = SmNoticeBoard::find($noticeRequestForm->notice_id);

            if (property_exists($noticeRequestForm, 'is_published') && $noticeRequestForm->is_published !== null) {
                $noticeData->is_published = $noticeRequestForm->is_published;
            }

            $noticeData->notice_title = $noticeRequestForm->notice_title;
            $noticeData->notice_message = $noticeRequestForm->notice_message;

            $noticeData->notice_date = date('Y-m-d', strtotime($noticeRequestForm->notice_date));
            $noticeData->publish_on = date('Y-m-d', strtotime($noticeRequestForm->publish_on));
            $noticeData->notice_date = Carbon::createFromFormat('m/d/Y', $noticeRequestForm->notice_date)->format('Y-m-d');
            $noticeData->publish_on = Carbon::createFromFormat('m/d/Y', $noticeRequestForm->publish_on)->format('Y-m-d');
            $noticeData->inform_to = json_encode($noticeRequestForm->role);
            $noticeData->updated_by = auth()->user()->id;
            $noticeData->is_published = $noticeRequestForm->is_published ? 1 : 0;
            $noticeData->update();

            if ($noticeRequestForm->role !== null) {

                foreach ($noticeRequestForm->role as $role) {
                    $users = User::where('role_id', $role)->get();
                    foreach ($users as $user) {
                        $notification = new SmNotification();
                        $notification->role_id = $role;
                        $notification->message = $noticeRequestForm->notice_title;
                        $notification->date = $noticeData->notice_date;
                        $notification->user_id = $user->id;
                        $notification->url = 'notice-list';
                        $notification->school_id = Auth::user()->school_id;
                        if (moduleStatusCheck('University')) {
                            $notification->un_academic_id = getAcademicId();
                        } else {
                            $notification->academic_id = getAcademicId();
                        }

                        $notification->save();
                    }
                }
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('notice-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteNoticeView(Request $request, $id)
    {
        /*
        try {
        */
            return view('backEnd.communicate.deleteNoticeView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteNotice(Request $request, $id)
    {
        /*
        try {
        */
            SmNoticeBoard::destroy($id);
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
