<?php

namespace App\Http\Controllers\api\v2\Dormitory;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\StudentDormitoryResource;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\SchoolScope;
use App\SmRoomList;
use App\SmStudent;
use Illuminate\Http\Request;

class DormitoryController extends Controller
{
    public function studentDormitory(Request $request)
    {
        $student_detail = SmStudent::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($request->student_id);

        $room_lists = SmRoomList::withoutGlobalScope(ActiveStatusSchoolScope::class)
            ->with('dormitory')
            ->where('active_status', 1)
            ->where('id', $student_detail->room_id)
            ->where('school_id', auth()->user()->school_id)
            ->groupBy('dormitory_id')
            ->get();

        $anonymousResourceCollection = StudentDormitoryResource::collection($room_lists);

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
                'message' => 'Dormitory list',
            ];
        }

        return response()->json($response);
    }
}
