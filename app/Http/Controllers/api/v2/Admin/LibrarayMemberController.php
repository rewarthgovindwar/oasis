<?php

namespace App\Http\Controllers\api\v2\Admin;

use App\Http\Controllers\Admin\StudentInfo\SmStudentReportController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Scopes\SchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmAcademicYear;
use App\SmClass;
use App\SmClassSection;
use App\SmLibraryMember;
use App\SmParent;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use Exception;
use Illuminate\Http\Request;
use Modules\RolePermission\Entities\InfixRole;

class LibrarayMemberController extends Controller
{
    public function roleItems()
    {
        $data = InfixRole::where('school_id', auth()->user()->school_id)->orWhere('type', 'System')->select('id', 'name')->get();

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
                'message' => 'Your role list',
            ];
        }

        return response()->json($response);
    }

    public function userNameList(Request $request)
    {
        if ($request->role_id !== 3 || $request->role_id !== 2) {
            $allStaffs = SmStaff::whereRole($request->role_id)->where('school_id', auth()->user()->school_id)->select('id', 'full_name', 'user_id')->get();
            $data = [];
            foreach ($allStaffs as $allStaff) {
                $data[] = SmStaff::where('id', $allStaff->id)->where('school_id', auth()->user()->school_id)->select('id', 'full_name', 'user_id')->first();
            }
        } else {
            $data = SmParent::where('active_status', 1)->where('school_id', auth()->user()->school_id)->select('id', 'fathers_name', 'user_id')->get();
        }

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'User name list',
        ];

        return response()->json($response);
    }

    public function classList(Request $request)
    {
        $data = [];
        if ($request->role_id == 3 || $request->role_id == 2) {
            $data = SmClass::withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id)->select('id', 'class_name')->get();
        }

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
                'message' => 'Your class list',
            ];
        }

        return response()->json($response);
    }

    public function sectionList(Request $request)
    {
        $sectionIds = SmClassSection::where('class_id', $request->class_id)
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR())
            ->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();

        $promote_sections = [];
        foreach ($sectionIds as $sectionId) {
            $promote_sections[] = SmSection::where('id', $sectionId->section_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->first(['id', 'section_name']);
        }

        if ($promote_sections == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $promote_sections,
                'message' => 'Class section list',
            ];
        }

        return response()->json($response);
    }

    public function studentList(Request $request)
    {
        $student_ids = SmStudentReportController::classSectionStudent($request);
        $students = SmStudent::withoutGlobalScope(SchoolScope::class)->with('parents')
            ->whereIn('id', $student_ids)
            ->where('active_status', 1)
            ->where('school_id', auth()->user()->school_id)
            ->get()->map(function ($student): array {
                return [
                    'id' => (int) $student->id,
                    'full_name' => (string) $student->full_name,
                    'user_id' => (int) $student->user_id,
                ];
            });

        if (! $students) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $students,
                'message' => 'Your student list',
            ];
        }

        return response()->json($response);
    }

    public function parentList(Request $request)
    {
        $student_ids = SmStudentReportController::classSectionStudent($request);
        $students = SmStudent::withoutGlobalScope(SchoolScope::class)->with('parents')
            ->whereIn('id', $student_ids)->where('active_status', 1)
            ->where('school_id', auth()->user()->school_id)
            ->get()->map(function ($student): array {
                return [
                    'id' => (int) $student->id,
                    'parent_name' => (string) $student->parents->fathers_name ?? (string) $student->parents->guardians_name,
                    'user_id' => (int) $student->parents->user_id,
                ];
            });

        if (! $students) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $students,
                'message' => 'Parent list',
            ];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        try {

            $this->validate($request, [
                'member_type' => 'required',
                'student' => 'required_if:member_type,2',
                'parent' => 'required_if:member_type,3',
                'member_ud_id' => 'required|max:120|unique:sm_library_members,member_ud_id',
            ], [
                'member_ud_id.required' => 'The Member id is required',
                'member_ud_id.unique' => 'The Member id must be an unique value',
            ]);

            if (! empty($request->student)) {
                $student = SmStudent::where('id', $request->student)->where('school_id', auth()->user()->school_id)->first();
                $student_staff_id = $student->user_id;
            }

            if (! empty($request->parent)) {
                $parent = SmStudent::whereHas('parents', function ($q) use ($request): void {
                    $q->where('user_id', $request->parent);
                })->with('parents')->where('school_id', auth()->user()->school_id)->first();
                $student_staff_id = $parent->parents->user_id;
            }

            if (! empty($request->staff)) {
                $student_staff_id = $request->staff;
            }

            $user = Auth()->user();
            $user_id = $user ? $user->id : $request->user_id;
            $isExitMember = SmLibraryMember::where('student_staff_id', $student_staff_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id)->status()->first();
            if ($isExitMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member already exists',
                    'data' => [],
                ]);
            }

            // if (!empty($isExitMember)) {
            //     $members = $isExitMember;
            //     $members->active_status = 1;
            //     $members->update();
            // }

            $smLibraryMember = new SmLibraryMember();
            $smLibraryMember->member_type = $request->member_type;
            $smLibraryMember->student_staff_id = $student_staff_id;
            $smLibraryMember->member_ud_id = $request->member_ud_id;
            $smLibraryMember->created_by = $user_id;
            $smLibraryMember->school_id = auth()->user()->school_id;
            $smLibraryMember->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $smLibraryMember->save();

            if ($request->member_type == 2) {
                try {
                    $data['class_id'] = $smLibraryMember->studentDetails->studentRecord->class_id;
                    $data['section_id'] = $smLibraryMember->studentDetails->studentRecord->section_id;
                    $records = $this->studentRecordInfo($data['class_id'], $data['section_id'])->pluck('studentDetail.user_id');
                    $this->sent_notifications('Add_Library_Member', $records, $data, ['Student', 'Parent']);
                } catch (Exception $e) {
                    //
                }
            }

            $data = [
                'id' => $smLibraryMember->id,
                'student' => $smLibraryMember->created_by,
                'user_id' => $smLibraryMember->created_by,
                'parent' => $smLibraryMember->student_staff_id,
                'staff' => $smLibraryMember->student_staff_id,
                'member_type' => $smLibraryMember->member_type,
                'member_ud_id' => $smLibraryMember->member_ud_id,
                'class_id' => (int) $request->class_id,
                'section_id' => (int) $request->section_id,
            ];

            $userName = User::select('full_name')->find($smLibraryMember->student_staff_id)->full_name;

            if (! $smLibraryMember) {
                $response = [
                    'success' => false,
                    'data' => null,
                    'message' => 'Operation failed',
                ];
            } else {
                $response = [
                    'success' => true,
                    'data' => null,
                    'message' => $userName.' has added as library member',
                ];
            }

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationException->validator->errors()->first(),
            ]);
        }
    }
}
