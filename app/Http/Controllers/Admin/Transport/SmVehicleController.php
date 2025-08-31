<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Transport\SmVehicleRequest;
use App\SmStaff;
use App\SmVehicle;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmVehicleController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $drivers = SmStaff::whereRole(9)->get();
            $assign_vehicles = SmVehicle::get();

            return view('backEnd.transport.vehicle', ['assign_vehicles' => $assign_vehicles, 'drivers' => $drivers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmVehicleRequest $smVehicleRequest)
    {
        /*
        try {
        */
            $smVehicle = new SmVehicle();
            $smVehicle->vehicle_no = $smVehicleRequest->vehicle_number;
            $smVehicle->vehicle_model = $smVehicleRequest->vehicle_model;
            $smVehicle->made_year = $smVehicleRequest->year_made;
            $smVehicle->driver_id = $smVehicleRequest->driver_id;
            $smVehicle->note = $smVehicleRequest->note;
            $smVehicle->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smVehicle->un_academic_id = getAcademicId();
            } else {
                $smVehicle->academic_id = getAcademicId();
            }

            $smVehicle->save();

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
            $drivers = SmStaff::whereRole(9)->get();
            $assign_vehicle = SmVehicle::find($id);
            $assign_vehicles = SmVehicle::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.transport.vehicle', ['assign_vehicle' => $assign_vehicle, 'assign_vehicles' => $assign_vehicles, 'drivers' => $drivers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmVehicleRequest $smVehicleRequest, $id)
    {
        /*
        try {
        */
            $assign_vehicle = SmVehicle::find($smVehicleRequest->id);
            $assign_vehicle->vehicle_no = $smVehicleRequest->vehicle_number;
            $assign_vehicle->vehicle_model = $smVehicleRequest->vehicle_model;
            $assign_vehicle->made_year = $smVehicleRequest->year_made;
            $assign_vehicle->driver_id = $smVehicleRequest->driver_id;
            $assign_vehicle->note = $smVehicleRequest->note;
            if (moduleStatusCheck('University')) {
                $assign_vehicle->un_academic_id = getAcademicId();
            }

            $assign_vehicle->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('vehicle');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        $tables = \App\tableList::getTableList('vehicle_id', $id);
        /*
        try {
        */
            if ($tables == null) {
                SmVehicle::destroy($id);

                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            } else {
                $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                Toastr::error($msg, 'Failed');
                return redirect()->back();
            }
        /*
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        } catch (Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
