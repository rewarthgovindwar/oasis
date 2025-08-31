<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmEvent;
use App\SmGeneralSettings;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmEventController extends Controller
{

    public function index(Request $request)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($events, null);
            }

            $events = SmEvent::all();

            return view('backEnd.events.eventsList', ['events' => $events]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'event_title' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'event_des' => 'required',
            'event_location' => 'required',
            'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
            $file = $request->file('upload_file_name');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                return redirect()->back();
            }

            $fileName = '';
            if ($request->file('upload_file_name') !== '') {
                $file = $request->file('upload_file_name');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/events/', $fileName);
                $fileName = 'public/uploads/events/'.$fileName;
            }

            $user = Auth()->user();

            $login_id = $user ? $user->id : $request->login_id;

            $smEvent = new SmEvent();
            $smEvent->event_title = $request->event_title;
            $smEvent->event_des = $request->event_des;
            $smEvent->event_location = $request->event_location;
            $smEvent->from_date = date('Y-m-d', strtotime($request->from_date));
            $smEvent->to_date = date('Y-m-d', strtotime($request->to_date));
            $smEvent->created_by = $login_id;
            $smEvent->uplad_image_file = $fileName;
            $results = $smEvent->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New Event has been added successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $editData = SmEvent::find($id);
            $events = SmEvent::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['events'] = $events->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.events.eventsList', ['editData' => $editData, 'events' => $events]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'event_title' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'event_des' => 'required',
            'event_location' => 'required',
            'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',

        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
            $file = $request->file('upload_file_name');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                return redirect()->back();
            }

            $fileName = '';
            if ($request->file('upload_file_name') !== '') {
                $eventFile = SmEvent::find($id);
                if ($eventFile->uplad_image_file !== '') {
                    unlink($eventFile->uplad_image_file);
                }

                $file = $request->file('upload_file_name');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/events/', $fileName);
                $fileName = 'public/uploads/events/'.$fileName;
            }

            $user = Auth()->user();

            $login_id = $user ? $user->id : $request->login_id;

            $events = SmEvent::find($id);
            $events->event_title = $request->event_title;
            $events->event_des = $request->event_des;
            $events->event_location = $request->event_location;
            $events->from_date = date('Y-m-d', strtotime($request->from_date));
            $events->to_date = date('Y-m-d', strtotime($request->to_date));
            $events->updated_by = $login_id;
            $events->uplad_image_file = $fileName;
            $results = $events->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Event has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect('event');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id): void
    {
        //
    }

    public function deleteEventView(Request $request, $id)
    {
        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.events.deleteEventView', ['id' => $id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function deleteEvent(Request $request, $id)
    {

        try {
            $result = SmEvent::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Event has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('event');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }
}
