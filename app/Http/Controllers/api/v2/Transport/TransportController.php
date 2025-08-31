<?php

namespace App\Http\Controllers\api\v2\Transport;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\StudentTransportResource;
use App\Scopes\SchoolScope;
use App\SmAssignVehicle;
use App\SmStudent;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function studentTransport(Request $request)
    {

        $user = SmStudent::withoutGlobalScope(SchoolScope::class)
            ->with(['user' => function ($q): void {
                $q->where('school_id', auth()->user()->school_id);
            }])
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($request->student_id);

        $routes = SmAssignVehicle::with('route', 'vehicle')
            ->join('sm_vehicles', 'sm_assign_vehicles.vehicle_id', 'sm_vehicles.id')
            ->join('sm_students', 'sm_vehicles.id', 'sm_students.vechile_id')
            ->where('sm_assign_vehicles.active_status', 1)
            ->where('sm_students.user_id', $user->user->id)
            ->where('sm_assign_vehicles.school_id', auth()->user()->school_id)
            ->get();

        $anonymousResourceCollection = StudentTransportResource::collection($routes);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Transport list',
            ];
        }

        return response()->json($response);
    }
}
