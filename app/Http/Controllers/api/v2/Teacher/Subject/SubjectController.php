<?php

namespace App\Http\Controllers\api\v2\Teacher\Subject;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\Teacher\Subject\SubjectListResource;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmSubject;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = SmSubject::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id)->latest('id')->get();
        $anonymousResourceCollection = SubjectListResource::collection($subjects);
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
                'message' => 'Subject list',
            ];
        }

        return response()->json($response);
    }
}
