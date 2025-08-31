<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmClassTime;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\SmExamTimeRequest;
use App\Http\Requests\Admin\Academics\SmClassTimeRequest;

class SmClassTimeController extends Controller
{
    public $date;

    public function __construct()
    {
        $this->date = generalSetting()->academic_Year->year;

    }

    public function index(Request $request)
    {
        $class_times = SmClassTime::where('type', 'class')->latest()->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($class_times, null);
        }

        return view('backEnd.academics.class_time', ['class_times' => $class_times]);
    }

    public function store(SmClassTimeRequest $smClassTimeRequest)
    {

        $user = Auth::user();
        $smClassTime = new SmClassTime();
        $smClassTime->type = 'class';
        $smClassTime->period = $smClassTimeRequest->period;
        $smClassTime->start_time = date('H:i:s', strtotime($smClassTimeRequest->start_time));
        $smClassTime->end_time = date('H:i:s', strtotime($smClassTimeRequest->end_time));
        $smClassTime->is_break = $smClassTimeRequest->is_break;
        $smClassTime->school_id = $user->school_id;
        $smClassTime->academic_id = getAcademicId();
        $result = $smClassTime->save();
        $type = $smClassTimeRequest->time_type;
        if (ApiBaseMethod::checkUrl($smClassTimeRequest->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse($type, 'time has been created successfully');
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again.');

        }

        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
    }

    public function show($id = null)
    {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
            $class_time = SmClassTime::find($id);
            $class_times = SmClassTime::where('type', 'class')->latest()->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['class_time'] = $class_time->toArray();
                $data['class_times'] = $class_times->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class_time', ['class_time' => $class_time, 'class_times' => $class_times]);
        /*
        } catch (Exception $exception) {

            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        } */
    }

    public function update(SmClassTimeRequest $smClassTimeRequest, $id)
    {

       
            $class_time = SmClassTime::find($smClassTimeRequest->id);

            $class_time->type = 'class';
            $class_time->period = $smClassTimeRequest->period;
            $class_time->start_time = date('H:i:s', strtotime($smClassTimeRequest->start_time));
            $class_time->end_time = date('H:i:s', strtotime($smClassTimeRequest->end_time));
            $class_time->is_break = $smClassTimeRequest->is_break;
            $result = $class_time->save();

            $type = $smClassTimeRequest->time_type;

            if (ApiBaseMethod::checkUrl($smClassTimeRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse($type, 'Class Room has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');
            return redirect('class-time');
       
    }

    public function destroy(Request $request, $id)
    {
       
            $class_id_key = 'class_period_id';
            $exam_id_key = 'exam_period_id';

            $class = \App\tableList::getTableList($class_id_key, $id);
            $exam = \App\tableList::getTableList($exam_id_key, $id);
            $tables = $class.''.$exam;

                if ($tables == null) {

                    $delete_query = SmClassTime::destroy($id);

                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($delete_query) {
                            return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                        }

                        return ApiBaseMethod::sendError('Something went wrong, please try again.');

                    }

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
    }

    public function examTime(Request $request)
    {
        /*
        try {
        */
            $class_times = SmClassTime::where('type', 'exam')->latest()->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($class_times, null);
            }
            return view('backEnd.academics.exam_time', ['class_times' => $class_times]);
        /*
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examtimeSave(SmExamTimeRequest $smExamTimeRequest)
    {

       
            $smClassTime = new SmClassTime();
            $smClassTime->type = 'exam';
            $smClassTime->period = $smExamTimeRequest->period;
            $smClassTime->start_time = date('H:i:s', strtotime($smExamTimeRequest->start_time));
            $smClassTime->end_time = date('H:i:s', strtotime($smExamTimeRequest->end_time));
            $smClassTime->school_id = Auth::user()->school_id;
            $smClassTime->academic_id = getAcademicId();
            $result = $smClassTime->save();

            $type = $smExamTimeRequest->time_type;

            if (ApiBaseMethod::checkUrl($smExamTimeRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse($type, 'time has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        
    }

    public function examTimeEdit(Request $request, $id)
    {
        /*
        try { */
            $class_time = SmClassTime::where('type', 'exam')->find($id);
            $class_times = SmClassTime::where('type', 'exam')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['class_time'] = $class_time->toArray();
                $data['class_times'] = $class_times->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.exam_time', ['class_time' => $class_time, 'class_times' => $class_times]);
        /*
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function examTimeUpdate(SmExamTimeRequest $smExamTimeRequest, $id)
    {

       
            // $class_time = SmClassTime::find($request->id);
            $class_time = SmClassTime::where('type', 'exam')->find($id);
            $class_time->type = 'exam';
            $class_time->period = $smExamTimeRequest->period;
            $class_time->start_time = date('H:i:s', strtotime($smExamTimeRequest->start_time));
            $class_time->end_time = date('H:i:s', strtotime($smExamTimeRequest->end_time));
            $result = $class_time->save();

            $type = $smExamTimeRequest->time_type;

            if (ApiBaseMethod::checkUrl($smExamTimeRequest->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse($type, 'Class Room has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect('exam-time');
       
    }
}
