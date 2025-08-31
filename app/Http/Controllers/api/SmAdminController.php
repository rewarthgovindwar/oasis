<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\SmComplaint;
use App\SmGeneralSettings;
use App\SmVisitor;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmAdminController extends SmBaseController
{
    // api login

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        try {
            if ($validator->fails()) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            $credentials = request(['email', 'password']);
            if (! Auth::attempt($credentials)) {
                return ApiBaseMethod::sendError(['error' => 'Unauthorised']);
            }

            $user = $request->user();
            $token = $user->createToken('jubaer')->accessToken;

            return ApiBaseMethod::sendResponse(['token' => 'Bearer '.$token], $msg = 'You are logged in');
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                $user->device_token = null;
                $user->save();
                $isUser = $user->token()->revoke();
                if ($isUser) {
                    $data['message'] = 'Successfully logged out.';

                    return ApiBaseMethod::sendResponse($data, null);
                }
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
    }

    // visitor method
    public function visitor()
    {
        $visitors = SmVisitor::all();

        return $this->sendResponse($visitors->toArray(), 'Visitors retrieved successfully.');
    }

    public function visitorStore(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
            'phone' => 'required',
            'purpose' => 'required',
            'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
        $file = $request->file('file');
        $fileSize = filesize($file);
        $fileSizeKb = ($fileSize / 1000000);
        if ($fileSizeKb >= $maxFileSize) {
            Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

            return redirect()->back();
        }

        $fileName = '';
        if ($request->file('file') !== '') {
            $file = $request->file('file');
            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
            $file->move('public/uploads/visitor/', $fileName);
            $fileName = 'public/uploads/visitor/'.$fileName;
        }

        $smVisitor = new SmVisitor();

        $smVisitor->name = $request->name;
        $smVisitor->phone = $request->phone;
        $smVisitor->visitor_id = $request->visitor_id;
        $smVisitor->no_of_person = $request->no_of_person;
        $smVisitor->purpose = $request->purpose;
        $smVisitor->date = date('Y-m-d', strtotime($request->date));
        $smVisitor->in_time = $request->in_time;
        $smVisitor->out_time = $request->out_time;
        $smVisitor->file = $fileName;

        $result = $smVisitor->save();

        if ($result) {
            return $this->sendResponse(null, 'Visitor has been created successfully.');
        }

        return $this->sendError('Something went wrong, please try again.');

    }

    public function visitorEdit($id)
    {
        $visitor = SmVisitor::find($id);

        if (is_null($visitor)) {
            return $this->sendError('Product not found.');
        }

        return $this->sendResponse($visitor->toArray(), 'Visitor retrieved successfully.');
    }

    public function visitorUpdate(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'phone' => 'required',
            'purpose' => 'required',
            'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
        $file = $request->file('file');
        $fileSize = filesize($file);
        $fileSizeKb = ($fileSize / 1000000);
        if ($fileSizeKb >= $maxFileSize) {
            Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

            return redirect()->back();
        }

        $fileName = '';
        if ($request->file('file') !== '') {
            $visitor = SmVisitor::find($request->id);
            if ($visitor->file !== '') {
                $path = url('/').'/public/uploads/visitor/'.$visitor->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $file = $request->file('file');
            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
            $file->move('public/uploads/visitor/', $fileName);
            $fileName = 'public/uploads/visitor/'.$fileName;
        }

        $visitor = SmVisitor::find($request->id);

        $visitor->name = $request->name;
        $visitor->phone = $request->phone;
        $visitor->visitor_id = $request->visitor_id;
        $visitor->no_of_person = $request->no_of_person;
        $visitor->purpose = $request->purpose;
        $visitor->date = date('Y-m-d', strtotime($request->date));
        $visitor->in_time = $request->in_time;
        $visitor->out_time = $request->out_time;

        $visitor->file = $fileName;

        $result = $visitor->save();

        if ($result) {
            return $this->sendResponse(null, 'Visitor has been updated successfully.');
        }

        return $this->sendError('Something went wrong, please try again.');

    }

    public function visitorDelete($id)
    {
        $visitor = SmVisitor::find($id);
        if ($visitor->file !== '' && file_exists($visitor->file)) {
            unlink($visitor->file);
        }

        $result = $visitor->delete();

        if ($result) {
            return $this->sendResponse(null, 'Visitor has been deleted successfully.');
        }

        return $this->sendError('Something went wrong, please try again.');

    }

    // complaint method
    public function complaint()
    {
        $complaints = SmComplaint::all();

        return $this->sendResponse($complaints->toArray(), 'Complaint retrieved successfully.');
    }

    public function complaintStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'complaint_by' => 'required',
            'complaint_type' => 'required',
            'phone' => 'required',
            'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
        $file = $request->file('file');
        $fileSize = filesize($file);
        $fileSizeKb = ($fileSize / 1000000);
        if ($fileSizeKb >= $maxFileSize) {
            Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

            return redirect()->back();
        }

        $fileName = '';
        if ($request->file('file') !== '') {
            $file = $request->file('file');
            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
            $file->move('public/uploads/complaint/', $fileName);
            $fileName = 'public/uploads/complaint/'.$fileName;
        }

        $smComplaint = new SmComplaint();
        $smComplaint->complaint_by = $request->complaint_by;
        $smComplaint->complaint_type = $request->complaint_type;
        $smComplaint->complaint_source = $request->complaint_source;
        $smComplaint->phone = $request->phone;
        $smComplaint->date = date('Y-m-d', strtotime($request->date));
        $smComplaint->description = $request->description;
        $smComplaint->action_taken = $request->action_taken;
        $smComplaint->assigned = $request->assigned;
        $smComplaint->file = $fileName;

        $result = $smComplaint->save();

        if ($result) {
            return $this->sendResponse(null, 'Complaint has been created successfully.');
        }

        return $this->sendError('Something went wrong, please try again.');

    }
}
