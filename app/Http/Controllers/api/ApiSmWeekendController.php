<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmWeekend;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmWeekendController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $weekends = SmWeekend::where('school_id', 1)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($weekends, null);
            }

            return view('backEnd.systemSettings.weekend', ['weekends' => $weekends]);
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
        try {
            $weekend = SmWeekend::find(1);
            $weekend->saturday = property_exists($request, 'saturday') && $request->saturday !== null ? 1 : 0;
            $weekend->sunday = property_exists($request, 'sunday') && $request->sunday !== null ? 2 : 0;
            $weekend->monday = property_exists($request, 'monday') && $request->monday !== null ? 3 : 0;
            $weekend->tuesday = property_exists($request, 'tuesday') && $request->tuesday !== null ? 4 : 0;
            $weekend->wednesday = property_exists($request, 'wednesday') && $request->wednesday !== null ? 5 : 0;
            $weekend->thursday = property_exists($request, 'thursday') && $request->thursday !== null ? 6 : 0;
            $weekend->friday = property_exists($request, 'friday') && $request->friday !== null ? 7 : 0;
            $result = $weekend->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Weekend has been added successfully.');
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
            $editData = SmWeekend::find($id);
            $weekends = SmWeekend::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['editData'] = $editData->toArray();
                $data['weekends'] = $weekends->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.systemSettings.weekend', ['weekends' => $weekends, 'editData' => $editData]);
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
            'name' => 'required',
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
            $weekend = SmWeekend::find($request->id);
            $weekend->name = $request->name;

            $weekend->is_weekend = property_exists($request, 'weekend') && $request->weekend !== null ? 1 : 0;

            $result = $weekend->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Weekend has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('weekend');
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
}
