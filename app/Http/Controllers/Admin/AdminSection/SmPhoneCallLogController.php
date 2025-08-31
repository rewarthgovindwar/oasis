<?php

namespace App\Http\Controllers\Admin\AdminSection;

use Exception;
use DataTables;
use App\SmPhoneCallLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Admin\AdminSection\SmPhoneCallRequest;

class SmPhoneCallLogController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
            return view('backEnd.admin.phone_call');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmPhoneCallRequest $smPhoneCallRequest)
    {
        /*
        try {
        */
            $smPhoneCallLog = new SmPhoneCallLog();
            $smPhoneCallLog->name = $smPhoneCallRequest->name;
            $smPhoneCallLog->phone = $smPhoneCallRequest->phone;
            $smPhoneCallLog->date = date('Y-m-d', strtotime($smPhoneCallRequest->date));
            $smPhoneCallLog->description = $smPhoneCallRequest->description;
            $smPhoneCallLog->next_follow_up_date = date('Y-m-d', strtotime($smPhoneCallRequest->follow_up_date));
            $smPhoneCallLog->call_duration = $smPhoneCallRequest->call_duration;
            $smPhoneCallLog->call_type = $smPhoneCallRequest->call_type;
            $smPhoneCallLog->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smPhoneCallLog->un_academic_id = getAcademicId();
            } else {
                $smPhoneCallLog->academic_id = getAcademicId();
            }

            $smPhoneCallLog->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $phone_call_logs = SmPhoneCallLog::get();
            $phone_call_log = SmPhoneCallLog::find($id);

            return view('backEnd.admin.phone_call', ['phone_call_logs' => $phone_call_logs, 'phone_call_log' => $phone_call_log]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmPhoneCallRequest $smPhoneCallRequest, $id)
    {
        /*
        try {
        */
            $phone_call_log = SmPhoneCallLog::find($smPhoneCallRequest->id);
            $phone_call_log->name = $smPhoneCallRequest->name;
            $phone_call_log->phone = $smPhoneCallRequest->phone;
            $phone_call_log->date = date('Y-m-d', strtotime($smPhoneCallRequest->date));
            $phone_call_log->description = $smPhoneCallRequest->description;
            $phone_call_log->next_follow_up_date = date('Y-m-d', strtotime($smPhoneCallRequest->follow_up_date));
            $phone_call_log->call_duration = $smPhoneCallRequest->call_duration;
            $phone_call_log->call_type = $smPhoneCallRequest->call_type;
            if (moduleStatusCheck('University')) {
                $phone_call_log->un_academic_id = getAcademicId();
            }

            $phone_call_log->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('phone-call');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request)
    {
        /*
        try {
        */
            $phone_call_log = SmPhoneCallLog::find($request->id);
            $result = $phone_call_log->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect('phone-call');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function phoneCallDatatable()
    {
        /*
        try {
        */
            $phone_call_logs = SmPhoneCallLog::query();

            return DataTables::of($phone_call_logs)
                ->addIndexColumn()
                ->addColumn('query_date', function ($row) {
                    return dateConvert(@$row->date);
                })
                ->addColumn('next_follow_up_date', function ($row) {
                    return dateConvert(@$row->next_follow_up_date);
                })
                ->addColumn('call_type', function ($row) {
                    return __('admin.'.($row->call_type == 'I' ? 'incoming' : 'outgoing'));
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                        
                                <div class="dropdown-menu dropdown-menu-right">'.
                            (userPermission('phone-call_edit') ? '<a class="dropdown-item" href="'.route('phone-call_edit', [$row->id]).'">'.app('translator')->get('common.edit').'</a>' : '').

                            (userPermission('phone-call_delete') ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                            '<a onclick="deleteQueryModal('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteCallLogModal" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                        '</div>
                            </div>';
                })
                ->rawColumns(['action', 'date'])
                ->make(true);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }
}
