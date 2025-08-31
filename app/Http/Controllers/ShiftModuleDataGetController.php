<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SmClass;
use App\Models\Shift;
use App\SmClassSection;
use App\SmAcademicYear;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Exception;

class ShiftModuleDataGetController extends Controller
{
    public function academicYearGetShift(Request $request)
    {
        try {
            $academic_year = SmAcademicYear::select('id')->where('school_id', Auth::user()->school_id)->where('id', $request->id)->first();

            $shifts = Shift::where('active_status', '=', '1')
                ->where('academic_id', $academic_year->id)
                ->where('school_id', Auth::user()->school_id)
                ->get(['name', 'id']);

            return response()->json([$shifts]);
        } catch (Exception $exception) {
            return response()->json('', 404);
        }
    }
    public function shiftGetClass(Request $request)
    {
        
        try {
            $shift_wise_class_ids = SmClassSection::when($request->academic_id, function ($query, $academicId) {
                return $query->where('academic_id', $academicId);
            })
            ->where('shift_id', $request->id)
            ->pluck('class_id');
            if ($shift_wise_class_ids->isEmpty()) {
                return response()->json([], 200);
            }
            $classes = SmClass::where('active_status', 1)
                ->whereIn('id', $shift_wise_class_ids)
                ->where('school_id', Auth::user()->school_id)
                ->withoutGlobalScope(StatusAcademicSchoolScope::class)
                ->get(['class_name', 'id']);
            
            return response()->json($classes);
        } catch (\Exception $e) {
            return response()->json('', 404);
        }
    }
}
