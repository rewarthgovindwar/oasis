<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmGeneralSettings;
use App\SmHoliday;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmHolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $holidays = SmHoliday::where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($holidays, null);
            }

            return view('backEnd.holidays.holidaysList', ['holidays' => $holidays]);
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
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'holiday_title' => 'required',
                'from_date' => 'required|before_or_equal:to_date',
                'to_date' => 'required',
                'user_id' => 'required',
                'details' => 'required',
                'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png',
            ]);
        } else {
            $validator = Validator::make($input, [
                'holiday_title' => 'required',
                'from_date' => 'required|before_or_equal:to_date',
                'to_date' => 'required',
                'details' => 'required',
                'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        }

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
                $file->move('public/uploads/holidays/', $fileName);
                $fileName = 'public/uploads/holidays/'.$fileName;
            }

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;

            $smHoliday = new SmHoliday();
            $smHoliday->holiday_title = $request->holiday_title;
            $smHoliday->details = $request->details;
            $smHoliday->from_date = date('Y-m-d', strtotime($request->from_date));
            $smHoliday->to_date = date('Y-m-d', strtotime($request->to_date));
            $smHoliday->created_by = $user_id;
            $smHoliday->upload_image_file = $fileName;
            $results = $smHoliday->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'New Holiday has been added successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

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
            $editData = SmHoliday::find($id);
            $holidays = SmHoliday::where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['holidays'] = $holidays->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.holidays.holidaysList', ['editData' => $editData, 'holidays' => $holidays]);
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
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'holiday_title' => 'required',
                'from_date' => 'required|before_or_equal:to_date',
                'to_date' => 'required',
                'user_id' => 'required',
                'details' => 'required',
                'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        } else {
            $validator = Validator::make($input, [
                'holiday_title' => 'required',
                'from_date' => 'required|before_or_equal:to_date',
                'to_date' => 'required',
                'details' => 'required',
                'upload_file_name' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        }

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
                $eventFile = SmHoliday::find($id);
                if ($eventFile->upload_image_file !== '') {
                    unlink($eventFile->upload_image_file);
                }

                $file = $request->file('upload_file_name');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/holidays/', $fileName);
                $fileName = 'public/uploads/holidays/'.$fileName;
            } else {
                $filesData = SmHoliday::find($id);
                $fileName = $filesData->upload_image_file;
            }

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;
            $holidays = SmHoliday::find($id);
            $holidays->holiday_title = $request->holiday_title;
            $holidays->details = $request->details;
            $holidays->from_date = date('Y-m-d', strtotime($request->from_date));
            $holidays->to_date = date('Y-m-d', strtotime($request->to_date));
            $holidays->updated_by = $user_id;
            $holidays->upload_image_file = $fileName;
            $results = $holidays->update();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Holiday has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($results) {
                Toastr::success('Operation successful', 'Success');

                return redirect('holiday');
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

    public function deleteHolidayView(Request $request, $id)
    {

        try {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($id, null);
            }

            return view('backEnd.holidays.deleteHolidayView', ['id' => $id]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteHoliday(Request $request, $id)
    {

        try {
            $holiday = SmHoliday::find($id);
            if ($holiday->upload_image_file !== '') {
                unlink($holiday->upload_image_file);
            }

            $result = $holiday->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Holiday has been deleted successfully.');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
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
}
