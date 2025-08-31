<?php

namespace App\Http\Controllers\api\v2\Admin;

use App\Http\Controllers\Controller;
use App\SmAcademicYear;
use App\SmStaff;
use App\SmVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function vehicleList()
    {
        $data = SmVehicle::where('school_id', auth()->user()->school_id)->select('id', 'vehicle_model', 'vehicle_no', 'made_year', 'note')->get();
        if (! $data) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Your vehicle list',
            ];
        }

        return response()->json($response);
    }

    public function driverList()
    {
        $data = SmStaff::whereRole(9)->where('school_id', auth()->user()->school_id)->select('id', 'full_name')->get();

        if (! $data) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Your driver list',
            ];
        }

        return response()->json($response);
    }

    public function storeVehicle(Request $request)
    {
        $school_id = auth()->user()->school_id;

        $this->validate($request, [
            'vehicle_number' => ['required', 'max:200', Rule::unique('sm_vehicles', 'vehicle_no')->where('school_id', $school_id)],
            'vehicle_model' => 'required|max:200',
            'year_made' => 'sometimes|nullable|max:10',
            'note' => 'sometimes|nullable',
            'driver_id' => 'required',
        ]);

        $smVehicle = new SmVehicle();
        $smVehicle->vehicle_no = $request->vehicle_number;
        $smVehicle->vehicle_model = $request->vehicle_model;
        $smVehicle->made_year = $request->year_made;
        $smVehicle->driver_id = $request->driver_id;
        $smVehicle->note = $request->note;
        $smVehicle->school_id = Auth::user()->school_id;
        $smVehicle->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
        $smVehicle->save();

        $data = SmVehicle::select('id', 'vehicle_no', 'vehicle_model', 'made_year', 'note')->find($smVehicle->id);

        if (! $data) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => [$data],
                'message' => 'The vehicle created successfully',
            ];
        }

        return response()->json($response);
    }
}
