<?php

namespace App\Http\Controllers\api\v2\Parent;

use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function childrens(Request $request)
    {
        $data = StudentRecord::with([
            'studentDetail' => function ($query) use ($request): void {
                $query->where('parent_id', $request->parent_id)
                    ->where('active_status', 1)
                    ->where('school_id', auth()->user()->school_id)
                    ->with('user');
            },
            'class' => function ($query): void {
                $query->withoutGlobalScopes([StatusAcademicSchoolScope::class, GlobalAcademicScope::class])
                    ->where('school_id', auth()->user()->school_id);
            },
            'section' => function ($query): void {
                $query->withoutGlobalScope(StatusAcademicSchoolScope::class)
                    ->where('school_id', auth()->user()->school_id);
            },
        ])
            ->whereHas('studentDetail', function ($query) use ($request): void {
                $query->where('parent_id', $request->parent_id)
                    ->where('active_status', 1)
                    ->where('school_id', auth()->user()->school_id);
            })
            ->where('is_promote', 0)
            ->where('active_status', 1)
            ->get()
            ->map(function ($record): array {
                return [
                    'student_id' => (int) $record->studentDetail->id,
                    'full_name' => (string) $record->studentDetail->full_name,
                    'class' => (string) @$record->class->class_name,
                    'section' => (string) @$record->section->section_name,
                    'image_url' => @$record->studentDetail->user->avatar_url
                        ? (string) asset(@$record->studentDetail->user->avatar_url)
                        : (string) null,
                ];
            });

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
                'message' => 'Children list',
            ];
        }

        return response()->json($response);
    }
}
