<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettings\SmHolidayRequest;
use App\SmHoliday;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmHolidayController extends Controller
{


    public function index(Request $request)
    {

        /*
        try {
        */
            $holidays = SmHoliday::get();

            return view('backEnd.holidays.holidaysList', ['holidays' => $holidays]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmHolidayRequest $smHolidayRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/holidays/';

            $smHoliday = new SmHoliday();
            $smHoliday->holiday_title = $smHolidayRequest->holiday_title;
            $smHoliday->details = $smHolidayRequest->details;
            $smHoliday->from_date = date('Y-m-d', strtotime($smHolidayRequest->from_date));
            $smHoliday->to_date = date('Y-m-d', strtotime($smHolidayRequest->to_date));
            $smHoliday->created_by = Auth::user()->id;
            $smHoliday->upload_image_file = fileUpload($smHolidayRequest->upload_file_name, $destination);
            $smHoliday->school_id = Auth::user()->school_id;
            $smHoliday->academic_id = getAcademicId();
            $results = $smHoliday->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit(Request $request, $id)
    {

        /*
        try {
        */
            $editData = SmHoliday::find($id);
            $holidays = SmHoliday::where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.holidays.holidaysList', ['editData' => $editData, 'holidays' => $holidays]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmHolidayRequest $smHolidayRequest, $id)
    {

        /*
        try {
        */

            $destination = 'public/uploads/holidays/';
            $holidays = SmHoliday::find($id);

            $holidays->holiday_title = $smHolidayRequest->holiday_title;
            $holidays->details = $smHolidayRequest->details;
            $holidays->from_date = date('Y-m-d', strtotime($smHolidayRequest->from_date));
            $holidays->to_date = date('Y-m-d', strtotime($smHolidayRequest->to_date));
            $holidays->updated_by = auth()->user()->id;
            $holidays->upload_image_file = fileUpdate($holidays->upload_image_file, $smHolidayRequest->upload_file_name, $destination);
            $holidays->update();

            Toastr::success('Operation successful', 'Success');

            return redirect('holiday');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteHolidayView(Request $request, $id)
    {
        /*try {
*/
            return view('backEnd.holidays.deleteHolidayView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteHoliday(Request $request, $id)
    {
        /*
        try {
        */

            $holidays = SmHoliday::find($id);
            if ($holidays->upload_image_file !== '') {
                unlink($holidays->upload_image_file);
            }

            $holidays->delete();

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
