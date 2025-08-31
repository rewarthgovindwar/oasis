<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Scopes\SchoolScope;
use App\SmStudent;
use Illuminate\Http\Request;

class ApiSmStudentController extends Controller
{
    public function searchStudent(Request $request)
    {
        $student_ids = StudentRecord::when($request->academic_year, function ($query) use ($request): void {
            $query->where('academic_id', $request->academic_year);
        })
            ->when($request->class, function ($query) use ($request): void {
                $query->where('class_id', $request->class);
            })
            ->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            })
            ->when($request->roll_no, function ($query) use ($request): void {
                $query->where('roll_no', $request->roll_no);
            })
            ->when(! $request->academic_year, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })
            ->where('school_id', auth()->user()->school_id)
            ->distinct('student_id')->pluck('student_id')->toArray();

        $studentDetails = SmStudent::whereIn('id', $student_ids)
            ->when($request->name, function ($q) use ($request): void {
                $q->where('full_name', 'like', '%'.$request->name.'%');
            })->get();
        // ->select('sm_students.id', 'student_photo', 'full_name', 'roll_no', 'user_id');

        $students = [];
        foreach ($studentDetails as $studentDetail) {

            $class_sec = [];
            foreach ($studentDetail->studentRecords as $classSec) {
                $class_sec[] = $classSec->class->class_name.'('.$classSec->section->section_name.'), ';
            }

            if ($request->class) {
                $sections = [];
                $class = $studentDetail->recordClass ? $studentDetail->recordClass->class->class_name : '';
                if ($request->section) {
                    $sections = $studentDetail->recordSection !== '' ? $studentDetail->recordSection->section->section_name : '';
                } else {
                    foreach ($studentDetail->recordClasses as $section) {
                        $sections[] = $section->section->section_name;
                    }

                }

                $class_sec = $class.'('.$sections.'), ';
            }

            $data['id'] = $studentDetail->id;
            $data['photo'] = $studentDetail->student_photo;
            $data['full_name'] = $studentDetail->full_name;
            $data['user_id'] = $studentDetail->user_id;
            $data['class_section'] = $class_sec;

            $students[] = $data;
        }

        $msg = count($studentDetails) > 0 ? 'Student Found' : 'Student Not Found';

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['students'] = $students;

            return ApiBaseMethod::sendResponse($data, $msg);
        }

        return null;
    }

    public function saas_searchStudent(Request $request, $school_id)
    {

        $student_ids = StudentRecord::where('is_promote', 0)->when($request->class, function ($query) use ($request): void {
            $query->where('class_id', $request->class);
        })
            ->when($request->section, function ($query) use ($request): void {
                $query->where('section_id', $request->section);
            })
            ->when($request->roll_no, function ($query) use ($request): void {
                $query->where('roll_no', $request->roll_no);
            })
            ->where('school_id', $school_id)
            ->distinct('student_id')->pluck('student_id')->toArray();

        $studentDetails = SmStudent::whereIn('id', $student_ids)
            ->when($request->name, function ($q) use ($request): void {
                $q->where('full_name', 'like', '%'.$request->name.'%');
            })->withOutGlobalScope(SchoolScope::class)->get();

        $students = [];
        foreach ($studentDetails as $studentDetail) {
            $class_sec = [];
            foreach ($studentDetail->studentRecords as $classSec) {

                $class_sec = $classSec->class->class_name.'('.$classSec->section->section_name.'), ';

            }

            $data['id'] = $studentDetail->id;
            $data['photo'] = $studentDetail->student_photo;
            $data['full_name'] = $studentDetail->full_name;
            $data['user_id'] = $studentDetail->user_id;
            $data['class_section'] = $class_sec;

            $students[] = $data;
        }

        $msg = count($studentDetails) > 0 ? 'Student Found' : 'Student Not Found';

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['students'] = $students;

            return ApiBaseMethod::sendResponse($data, $msg);
        }

        return null;
    }
}
