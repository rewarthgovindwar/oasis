<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmClassTime;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmClassTimeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $class_times = SmClassTime::where('type', 'class')->get();
            $class_times_exam = SmClassTime::where('type', 'exam')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($class_times, null);
            }

            return view('backEnd.academics.class_time', ['class_times' => $class_times, 'class_times_exam' => $class_times_exam]);
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
            'time_type' => 'required',
            'period' => 'required|max:200|unique:sm_class_times,period',
            'start_time' => 'required|before:end_time',
            'end_time' => 'required',
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
            $smClassTime = new SmClassTime();
            $smClassTime->type = $request->time_type;
            $smClassTime->period = $request->period;
            $smClassTime->start_time = date('H:i:s', strtotime($request->start_time));
            $smClassTime->end_time = date('H:i:s', strtotime($request->end_time));
            $result = $smClassTime->save();

            $type = $request->time_type;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse($type, 'time has been created successfully');
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
            $class_time = SmClassTime::find($id);
            $class_times = SmClassTime::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['class_time'] = $class_time->toArray();
                $data['class_times'] = $class_times->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.class_time', ['class_time' => $class_time, 'class_times' => $class_times]);
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
            'time_type' => 'required',
            'period' => 'required|max:200|unique:sm_class_times,period,'.$request->id,
            'start_time' => 'required|before:end_time',
            'end_time' => 'required',
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
            $class_time = SmClassTime::find($request->id);
            $class_time->type = $request->time_type;
            $class_time->period = $request->period;
            $class_time->start_time = date('H:i:s', strtotime($request->start_time));
            $class_time->end_time = date('H:i:s', strtotime($request->end_time));
            $result = $class_time->save();

            $type = $request->time_type;

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse($type, 'Class Room has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('class-time');
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
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        try {
            $class_id_key = 'class_period_id';
            $exam_id_key = 'exam_period_id';

            $class = \App\tableList::getTableList($class_id_key, $id);
            $exam = \App\tableList::getTableList($exam_id_key, $id);
            $tables = $class.''.$exam;
            // return $tables;
            try {
                $delete_query = SmClassTime::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    }

                    return ApiBaseMethod::sendError('Something went wrong, please try again.');

                }

                if ($delete_query) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();

            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
