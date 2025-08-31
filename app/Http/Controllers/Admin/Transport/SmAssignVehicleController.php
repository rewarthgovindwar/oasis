<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Transport\SmAssignVehicleRequest;
use App\SmAssignVehicle;
use App\SmRoute;
use App\SmVehicle;
use App\Traits\NotificationSend;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmAssignVehicleController extends Controller
{
    use NotificationSend;



    public function index(Request $request)
    {
        /*
        try {
        */
            $routes = SmRoute::get();
            $assign_vehicles = SmAssignVehicle::with('route', 'vehicle')->where('school_id', Auth::user()->school_id)->get();
            $vehicles = SmVehicle::select('id', 'vehicle_no')->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.transport.assign_vehicle', ['routes' => $routes, 'assign_vehicles' => $assign_vehicles, 'vehicles' => $vehicles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAssignVehicleRequest $smAssignVehicleRequest)
    {
        /*
        try {
        */
            $smAssignVehicle = new SmAssignVehicle();
            $smAssignVehicle->route_id = $smAssignVehicleRequest->route;
            $vehicles = '';
            $i = 0;
            foreach ($smAssignVehicleRequest->vehicles as $vehicle) {
                $i++;
                if ($i == 1) {
                    $vehicles .= $vehicle;
                } else {
                    $vehicles .= ',';
                    $vehicles .= $vehicle;
                }
            }

            $smAssignVehicle->vehicle_id = $vehicles;
            $smAssignVehicle->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAssignVehicle->un_academic_id = getAcademicId();
            } else {
                $smAssignVehicle->academic_id = getAcademicId();
            }

            $smAssignVehicle->save();

            // $data['vehicle_no'] = $assign_vehicle->vehicle->vehicle_no;
            // $data['route'] = $assign_vehicle->route->title;
            // $records = $this->studentRecordInfo(null, null)->pluck('studentDetail.user_id');
            // $this->sent_notifications('Assign_Vehicle', $records, $data, ['Student', 'Parent']);

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
            $routes = SmRoute::get();
            $assign_vehicles = SmAssignVehicle::with('route', 'vehicle')->where('school_id', Auth::user()->school_id)->get();
            $assign_vehicle = SmAssignVehicle::find($id);
            $vehiclesIds = explode(',', $assign_vehicle->vehicle_id);
            $vehicles = SmVehicle::select('id', 'vehicle_no')->get();

            return view('backEnd.transport.assign_vehicle', ['routes' => $routes, 'assign_vehicles' => $assign_vehicles, 'assign_vehicle' => $assign_vehicle, 'vehicles' => $vehicles, 'vehiclesIds' => $vehiclesIds]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmAssignVehicleRequest $smAssignVehicleRequest, $id)
    {
        /*
        try {
        */
            $assign_vehicle = SmAssignVehicle::find($id);
            $assign_vehicle->route_id = $smAssignVehicleRequest->route;
            $vehicles = '';
            $i = 0;
            foreach ($smAssignVehicleRequest->vehicles as $vehicle) {
                $i++;
                if ($i == 1) {
                    $vehicles .= $vehicle;
                } else {
                    $vehicles .= ',';
                    $vehicles .= $vehicle;
                }
            }

            $assign_vehicle->vehicle_id = $vehicles;
            if (moduleStatusCheck('University')) {
                $assign_vehicle->un_academic_id = getAcademicId();
            } else {
                $assign_vehicle->academic_id = getAcademicId();
            }

            $assign_vehicle->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('assign-vehicle');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request)
    {
        /*
        try {
        */
            SmAssignVehicle::where('id', $request->id)->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect('assign-vehicle');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
