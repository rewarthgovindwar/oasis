<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmClassTeacher;
use Illuminate\Http\Request;
use App\SmAssignClassTeacher;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Events\ClassTeacherGetAllStudent;
use App\Http\Requests\Admin\Academics\SmAssignClassTeacherRequest;

class SmAssignClassTeacherController extends Controller
{
    use NotificationSend;

    public function index(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::get();
            $teachers = SmStaff::status()->where(function ($q): void {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->get();
            $assign_class_teachers = SmAssignClassTeacher::with('class', 'section', 'classTeachers')->where('academic_id', getAcademicId())->status()->orderBy('class_id', 'ASC')->orderBy('section_id', 'ASC')->get();

            return view('backEnd.academics.assign_class_teacher', ['classes' => $classes, 'teachers' => $teachers, 'assign_class_teachers' => $assign_class_teachers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAssignClassTeacherRequest $smAssignClassTeacherRequest)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $query = SmAssignClassTeacher::where('active_status', 1)
                ->where('class_id', $smAssignClassTeacherRequest->class)
                ->where('section_id', $smAssignClassTeacherRequest->section)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id);

            if (shiftEnable()) {
                $query->where('shift_id', $smAssignClassTeacherRequest->shift);
            }

            $assigned_class_teacher = $query->first();

            if (empty($assigned_class_teacher)) {
                $smAssignClassTeacher = new SmAssignClassTeacher();
                $smAssignClassTeacher->class_id = $smAssignClassTeacherRequest->class;
                $smAssignClassTeacher->section_id = $smAssignClassTeacherRequest->section;
                $smAssignClassTeacher->school_id = $user->school_id;
                $smAssignClassTeacher->academic_id = getAcademicId();

                if (shiftEnable()) {
                    $smAssignClassTeacher->shift_id = $smAssignClassTeacherRequest->shift;
                }

                $smAssignClassTeacher->save();

                $smClassTeacher = new SmClassTeacher();
                $smClassTeacher->assign_class_teacher_id = $smAssignClassTeacher->id;
                $smClassTeacher->teacher_id = $smAssignClassTeacherRequest->teacher;
                $smClassTeacher->school_id = $user->school_id;
                $smClassTeacher->academic_id = getAcademicId();
                $smClassTeacher->save();

                event(new ClassTeacherGetAllStudent($smAssignClassTeacher, $smClassTeacher));
                DB::commit();

                $data['class_id'] = $smAssignClassTeacherRequest->class;
                $data['section_id'] = $smAssignClassTeacherRequest->section;
                $data['teacher_name'] = $smClassTeacher->teacher->full_name;

                if (shiftEnable()) {
                    $data['shift_id'] = $smAssignClassTeacherRequest->shift;
                }

                $this->sent_notifications('Assign_Class_Teacher', (array) $smClassTeacher->teacher->user_id, $data, ['Teacher']);

                $records = $this->studentRecordInfo(
                    $smAssignClassTeacherRequest->class,
                    $smAssignClassTeacherRequest->section,
                    shiftEnable() ? $smAssignClassTeacherRequest->shift : null
                )->pluck('studentDetail.user_id');

                $this->sent_notifications('Assign_Class_Teacher', $records, $data, ['Student', 'Parent']);
            } else {
                Toastr::warning('Class Teacher already assigned.', 'Warning');
                return redirect()->back();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (Exception $exception) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function edit(Request $request, $id)
    {

        /*
        try {
        */
            $classes = SmClass::get();
            $teachers = SmStaff::status()->where(function ($q): void {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            })->get();
            $assign_class_teachers = SmAssignClassTeacher::with('class', 'section', 'classTeachers')->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $assign_class_teacher = SmAssignClassTeacher::find($id);
            $sections = SmSection::get();

            $teacherId = [];
            foreach ($assign_class_teacher->classTeachers as $classTeacher) {
                $teacherId[] = $classTeacher->teacher_id;
            }

            return view('backEnd.academics.assign_class_teacher', ['assign_class_teacher' => $assign_class_teacher, 'classes' => $classes, 'teachers' => $teachers, 'assign_class_teachers' => $assign_class_teachers, 'sections' => $sections, 'teacherId' => $teacherId]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmAssignClassTeacherRequest $smAssignClassTeacherRequest, $id)
    {
        $user = Auth::user();
        $is_duplicate = SmAssignClassTeacher::where('school_id', $user->school_id)->where('academic_id', getAcademicId())->where('class_id', $smAssignClassTeacherRequest->class)->where('section_id', $smAssignClassTeacherRequest->section)->where('id', '!=', $smAssignClassTeacherRequest->id)->first();
        if ($is_duplicate) {
            Toastr::warning('Duplicate entry found!', 'Warning');

            return redirect()->back();
        }

        DB::beginTransaction();

        try {
            SmClassTeacher::where('assign_class_teacher_id', $smAssignClassTeacherRequest->id)->delete();

            $assign_class_teacher = SmAssignClassTeacher::find($smAssignClassTeacherRequest->id);
            $assign_class_teacher->class_id = $smAssignClassTeacherRequest->class;
            $assign_class_teacher->academic_id = getAcademicId();
            $assign_class_teacher->section_id = $smAssignClassTeacherRequest->section;
            if (shiftEnable()) {
                $assign_class_teacher->shift_id = $smAssignClassTeacherRequest->shift;
            } else {
                $assign_class_teacher->shift_id = null;
            }
            $assign_class_teacher->save();
            $assign_class_teacher_collection = $assign_class_teacher;
            $assign_class_teacher->toArray();

            $smClassTeacher = new SmClassTeacher();
            $smClassTeacher->assign_class_teacher_id = $assign_class_teacher->id;
            $smClassTeacher->teacher_id = $smAssignClassTeacherRequest->teacher;
            $smClassTeacher->school_id = $user->school_id;
            $smClassTeacher->academic_id = getAcademicId();
            $smClassTeacher->save();

            event(new ClassTeacherGetAllStudent($assign_class_teacher_collection, $smClassTeacher, 'update'));

            DB::commit();
            Toastr::success('Operation successful', 'Success');

            return redirect('assign-class-teacher');
        } catch (Exception $exception) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $id_key = 'assign_class_teacher_id';
            $tables = \App\tableList::getTableList($id_key, $id);

            try {
                DB::beginTransaction();

                SmClassTeacher::where('assign_class_teacher_id', $id)->delete();
                SmAssignClassTeacher::destroy($id);

                DB::commit();
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollback();
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
