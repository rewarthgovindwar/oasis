<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmStaff;
use App\SmVehicle;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmSmVehicleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $drivers = SmStaff::where([['active_status', 1], ['role_id', 9]])->get();
            $assign_vehicles = SmVehicle::get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['drivers'] = $drivers->toArray();
                $data['assign_vehicles'] = $assign_vehicles->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.transport.vehicle', ['assign_vehicles' => $assign_vehicles, 'drivers' => $drivers]);
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
            'vehicle_number' => 'required|unique:sm_vehicles,vehicle_no|max:200',
            'vehicle_model' => 'required|max:200',
            'year_made' => 'sometimes|nullable|max:10',
            'driver_id' => 'required',
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
            $smVehicle = new SmVehicle();
            $smVehicle->vehicle_no = $request->vehicle_number;
            $smVehicle->vehicle_model = $request->vehicle_model;
            if ($request->year_made) {
                $smVehicle->made_year = $request->year_made;
            }

            $smVehicle->driver_id = $request->driver_id;
            // $assign_vehicle->driver_license = $request->driver_license;
            // $assign_vehicle->driver_contact = $request->driver_contact;
            $smVehicle->note = $request->note;
            $result = $smVehicle->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Vehicle has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            // $drivers = SmStaff::where('active_status', 1)->get();
            $drivers = SmStaff::where([['active_status', 1], ['role_id', 9]])->get();
            $assign_vehicle = SmVehicle::find($id);
            $assign_vehicles = SmVehicle::get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['route'] = $drivers->toArray();
                $data['routes'] = $assign_vehicle;
                $data['routes'] = $assign_vehicles->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.transport.vehicle', ['assign_vehicle' => $assign_vehicle, 'assign_vehicles' => $assign_vehicles, 'drivers' => $drivers]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): void
    {
        //
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
                'vehicle_number' => 'required|max:200|unique:sm_vehicles,vehicle_no,'.$id,
                'vehicle_model' => 'required|max:200',
                'year_made' => 'sometimes|nullable|max:10',
                'id' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'vehicle_number' => 'required|max:200|unique:sm_vehicles,vehicle_no,'.$id,
                'vehicle_model' => 'required|max:200',
                'year_made' => 'sometimes|nullable|max:10',
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
            $assign_vehicle = SmVehicle::find($request->id);
            $assign_vehicle->vehicle_no = $request->vehicle_number;
            $assign_vehicle->vehicle_model = $request->vehicle_model;
            $assign_vehicle->made_year = $request->year_made;
            $assign_vehicle->driver_id = $request->driver_id;
            // $assign_vehicle->driver_license = $request->driver_license;
            // $assign_vehicle->driver_contact = $request->driver_contact;
            $assign_vehicle->note = $request->note;
            $result = $assign_vehicle->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Vehicle has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('vehicle');
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

        $tables = \App\tableList::getTableList('vehicle_id', $id);
        try {
            $vehicle = SmVehicle::destroy($id);
            if ($vehicle) {

                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($vehicle) {
                        return ApiBaseMethod::sendResponse(null, 'Vehicle has been deleted successfully');
                    }

                    return ApiBaseMethod::sendError('Something went wrong, please try again.');

                }

                if ($vehicle) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();

            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error('This item already used', 'Failed');

            return redirect()->back();
        } catch (Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }
}
