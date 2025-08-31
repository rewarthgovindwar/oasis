<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAssignClassTeacher;
use App\SmClass;
use App\SmClassTeacher;
use App\SmSection;
use App\SmStaff;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiSmAssignClassTeacherControler extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $teachers = SmStaff::where('active_status', 1)->where('role_id', 4)->get();
            $assign_class_teachers = SmAssignClassTeacher::where('active_status', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['classes'] = $classes->toArray();
                $data['teachers'] = $teachers->toArray();
                $data['assign_class_teachers'] = $assign_class_teachers->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.assign_class_teacher', ['classes' => $classes, 'teachers' => $teachers, 'assign_class_teachers' => $assign_class_teachers]);

        } catch (\Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make(
            $input,
            [
                'class' => 'required',
                'section' => 'required',
                'teacher' => 'required|array',
            ],
            [
                'teacher.required' => 'At least one checkbox required!',
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            $smAssignClassTeacher = new SmAssignClassTeacher();
            $smAssignClassTeacher->class_id = $request->class;
            $smAssignClassTeacher->section_id = $request->section;
            $smAssignClassTeacher->save();
            $smAssignClassTeacher->toArray();

            foreach ($request->teacher as $teacher) {
                $class_teacher = new SmClassTeacher();
                $class_teacher->assign_class_teacher_id = $smAssignClassTeacher->id;
                $class_teacher->teacher_id = $teacher;
                $class_teacher->save();
            }

            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Class Teacher has been Assigned successfully');
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        } catch (Exception $exception) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {

        try {
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $teachers = SmStaff::where('active_status', 1)->where(function ($q): void {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->get();
            $assign_class_teachers = SmAssignClassTeacher::where('active_status', 1)->where('academic_id', getAcademicId())->get();
            $assign_class_teacher = SmAssignClassTeacher::find($id);
            $sections = SmSection::where('active_status', '=', 1)->where('academic_id', getAcademicId())->get();

            $teacherId = [];
            foreach ($assign_class_teacher->classTeachers as $classTeacher) {
                $teacherId[] = $classTeacher->teacher_id;
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['assign_class_teacher'] = $assign_class_teacher;
                $data['classes'] = $classes->toArray();
                $data['teachers'] = $teachers->toArray();
                $data['assign_class_teachers'] = $assign_class_teachers->toArray();
                $data['sections'] = $sections->toArray();
                $data['teacherId'] = $teacherId;

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.academics.assign_class_teacher', ['assign_class_teacher' => $assign_class_teacher, 'classes' => $classes, 'teachers' => $teachers, 'assign_class_teachers' => $assign_class_teachers, 'sections' => $sections, 'teacherId' => $teacherId]);

        } catch (\Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $input = $request->all();
        $validator = Validator::make(
            $input,
            [
                'class' => 'required',
                'section' => 'required',
                'teacher' => 'required|array',
            ],
            [
                'teacher.required' => 'At least one checkbox required!',
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            SmClassTeacher::where('assign_class_teacher_id', $request->id)->delete();

            $assign_class_teacher = SmAssignClassTeacher::find($request->id);
            $assign_class_teacher->class_id = $request->class;
            $assign_class_teacher->section_id = $request->section;
            $assign_class_teacher->save();
            $assign_class_teacher->toArray();

            foreach ($request->teacher as $teacher) {
                $class_teacher = new SmClassTeacher();
                $class_teacher->assign_class_teacher_id = $assign_class_teacher->id;
                $class_teacher->teacher_id = $teacher;
                $class_teacher->save();
            }

            DB::commit();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse(null, 'Class Teacher has been updated successfully');
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('assign-class-teacher');
        } catch (Exception $exception) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $id_key = 'assign_class_teacher_id';
            $tables = \App\tableList::getTableList($id_key, $id);

            try {
                $delete_query = SmAssignClassTeacher::destroy($id);
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($delete_query) {
                        return ApiBaseMethod::sendResponse(null, 'Class Teacher has been deleted successfully');
                    }

                    return ApiBaseMethod::sendError('Something went wrong, please try again.');

                }

                if ($delete_query) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();

            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }

        } catch (\Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }
}
