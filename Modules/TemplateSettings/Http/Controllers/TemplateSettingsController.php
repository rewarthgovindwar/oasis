<?php

namespace Modules\TemplateSettings\Http\Controllers;

use App\InfixModuleManager;
use App\SmsTemplate;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TemplateSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            return view('templatesettings::index');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function about()
    {

        try {
            $data = InfixModuleManager::where('name', 'TemplateSettings')->first();

            return view('templatesettings::index', ['data' => $data]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function emailTemplate()
    {
        $emailTempletes = SmsTemplate::where('type', 'email')->where('school_id', auth()->user()->school_id)->get();

        return view('templatesettings::emailTemplate', ['emailTempletes' => $emailTempletes]);
    }

    public function emailTemplateUpdate(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'body' => 'required',
        ]);

        try {
            $updateData = SmsTemplate::find($request->id);
            $updateData->type = 'email';
            $updateData->subject = $request->subject;
            $updateData->body = $request->body;
            $updateData->status = $request->status ?: 0;
            $updateData->school_id = Auth::user()->school_id;
            $updateData->update();
            Toastr::success('Operation success', 'Success');

            return redirect()->route('templatesettings.email-template');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function smsTemplate()
    {
        try {
            $smsTemplates = SmsTemplate::where('type', 'sms')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('templatesettings::smsTemplate', ['smsTemplates' => $smsTemplates]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function smsTemplateUpdate(Request $request)
    {
        try {
            $updateData = SmsTemplate::find($request->id);
            $updateData->type = 'sms';
            $updateData->body = $request->body;
            $updateData->status = $request->status ? 1 : 0;
            $updateData->school_id = Auth::user()->school_id;
            $updateData->update();
            Toastr::success('Operation success', 'Success');

            return redirect()->route('templatesettings.sms-template');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
