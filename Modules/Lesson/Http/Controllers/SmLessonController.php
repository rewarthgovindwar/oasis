<?php

namespace Modules\Lesson\Http\Controllers;

use Exception;
use DataTables;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmSubject;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\SmLesson;
use Illuminate\Support\Facades\Config;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\Lesson\Entities\SmLessonTopic;
use Modules\University\Entities\UnSubject;
use Modules\Lesson\Entities\SmLessonTopicDetail;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmLessonController extends Controller
{
    

    public function index(){
        try {
            $data = $this->loadLesson();
            return view('lesson::lesson.add_new_lesson', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeLesson(Request $request)
    {
        if (moduleStatusCheck('University')) {
            $request->validate(
                [
                    'un_session_id' => 'required',
                    'un_faculty_id' => 'sometimes|nullable',
                    'un_department_id' => 'required',
                    'un_academic_id' => 'required',
                    'un_semester_id' => 'required',
                    'un_semester_label_id' => 'required',
                    'un_subject_id' => 'required',
                    'un_section_id' => 'sometimes|nullable',
                ],
            );
        } else {
            $request->validate(
                [
                    'class' => 'required',
                    'subject' => 'required',
                ],
            );
        }

        DB::beginTransaction();
        
        try {
            $sections = SmAssignSubject::where('class_id', $request->class)
                ->where('subject_id', $request->subject)
                ->when(shiftEnable(), function ($q) use ($request) {
                    return $q->where('shift_id', $request->shift);
                })
                ->get();

            if (moduleStatusCheck('University')) {
                if ($request->un_section_id) {
                    $sections = UnSubject::where('un_department_id', $request->un_department_id)
                        ->where('school_id', auth()->user()->school_id)
                        ->get();
                } else {
                    $sections = $request->un_section_id;
                }
            }

            foreach ($sections as $section) {
                foreach ($request->lesson as $lesson) {
                    $smLesson = new SmLesson;
                    $smLesson->lesson_title = $lesson;
                    $smLesson->class_id = $request->class;
                    $smLesson->subject_id = $request->subject;
                    $smLesson->section_id = $section->section_id;
                    $smLesson->shift_id = shiftEnable() ? $request->shift : null;
                    $smLesson->school_id = auth()->user()->school_id;
                    $smLesson->user_id = auth()->user()->id;
                    if (moduleStatusCheck('University')) {
                        $common = App::make(UnCommonRepositoryInterface::class);
                        $common->storeUniversityData($smLesson, $request);
                    } else {
                        $smLesson->academic_id = getAcademicId();
                    }

                    $smLesson->save();
                }
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function editLesson($class_id, $section_id, $subject_id, $shift_id = null)
    {
        try {
            $data = $this->loadLesson();
            $data['lesson'] = SmLesson::where([['class_id', $class_id], ['section_id', $section_id], ['subject_id', $subject_id]])
            ->when($shift_id, function ($query) use ($shift_id) {
                $query->where('shift_id', $shift_id);
            })->first();
            $data['lesson_detail'] = SmLesson::where([['class_id', $class_id], ['section_id', $section_id], ['subject_id', $subject_id]])->get();
            $data['class_id'] = $class_id;
            $data['section_id'] = $section_id;
            $data['shift_id'] = $shift_id;
            return view('lesson::lesson.edit_lesson', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editLessonForUniVersity($session_id, $faculty_id, $department_id, $academic_id, $semester_id, $semester_label_id, $subject_id = null)
    {
        try {
            $data = $this->loadLesson();
            $lesson = SmLesson::when($session_id, function ($query) use ($session_id): void {
                $query->where('un_session_id', $session_id);
            })->when($faculty_id !== 0, function ($query) use ($faculty_id): void {
                $query->where('un_faculty_id', $faculty_id);
            })->when($department_id, function ($query) use ($department_id): void {
                $query->where('un_department_id', $department_id);
            })->when($academic_id, function ($query) use ($academic_id): void {
                $query->where('un_academic_id', $academic_id);
            })->when($semester_id, function ($query) use ($semester_id): void {
                $query->where('un_semester_id', $semester_id);
            })->when($semester_label_id, function ($query) use ($semester_label_id): void {
                $query->where('un_semester_label_id', $semester_label_id);
            })->when($subject_id !== 0, function ($query) use ($subject_id): void {
                $query->where('un_subject_id', $subject_id);
            });
            $data['lesson_detail'] = $lesson->select(['lesson_title', 'active_status', 'class_id', 'section_id', 'subject_id', 'academic_id'])->get();
            $data['lesson'] = $lesson->first();
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($data['lesson']);

            return view('lesson::lesson.edit_lesson', $data);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function updateLesson(Request $request)
    {
        try {
            
            $existingLessons = SmLesson::whereIn('id', $request->lesson_detail_id)->get();
            foreach ($existingLessons as $key => $lesson) {
                $lesson->lesson_title = $request->lesson[$key];
                $lesson->shift_id = shiftEnable() ? $request->shift : null;
                $lesson->school_id = Auth::user()->school_id;
                $lesson->academic_id = getAcademicId();
                $lesson->user_id = Auth::user()->id;
                $lesson->save();
            }

            $newLessonCount = count($request->lesson) - count($existingLessons);

            if ($newLessonCount > 0) {
                $lastLessonId = SmLesson::orderBy('id', 'desc')->first()->id ?? 0;
                $counter = count($request->lesson);
                for ($i = count($existingLessons); $i < $counter; $i++) {
                    $newLesson = new SmLesson;
                    $newLesson->id = ++$lastLessonId;
                    $newLesson->lesson_title = $request->lesson[$i];
                    $newLesson->class_id = $existingLessons->first()->class_id;
                    $newLesson->subject_id = $existingLessons->first()->subject_id;
                    $newLesson->section_id = $existingLessons->first()->section_id;
                    $newLesson->shift_id = shiftEnable() ? $request->shift : null;
                    $newLesson->school_id = Auth::user()->school_id;
                    $newLesson->academic_id = getAcademicId();
                    $newLesson->user_id = Auth::user()->id;
                    $newLesson->save();
                }
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteLesson($id)
    {
        $lesson = SmLesson::find($id);
        $lesson_detail = SmLesson::where([
            ['class_id', $lesson->class_id],
            ['section_id', $lesson->section_id],
            ['subject_id', $lesson->subject_id],
        ])
        ->when(shiftEnable(), function ($q) use ($lesson) {
            return $q->where('shift_id', $lesson->shift_id);
        })
        ->get();
        foreach ($lesson_detail as $lesson_data) {
            SmLesson::destroy($lesson_data->id);
        }

        $SmLessonTopic = SmLessonTopic::where('lesson_id', $id)->get();
        if ($SmLessonTopic) {
            foreach ($SmLessonTopic as $t_data) {
                SmLessonTopic::destroy($t_data->id);
            }
        }

        $SmLessonTopicDetail = SmLessonTopicDetail::where('lesson_id', $id)->get();
        if ($SmLessonTopicDetail) {
            foreach ($SmLessonTopicDetail as $td_data) {
                SmLessonTopicDetail::destroy($td_data->id);
            }
        }

        $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
        if ($LessonPlanner) {
            foreach ($LessonPlanner as $lp_data) {
                LessonPlanner::destroy($lp_data->id);
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('lesson');
    }

    public function destroyLesson(Request $request)
    {
        $id = $request->id;
        return SmLesson::find($id);
    }

    public function deleteLessonItem($id)
    {
        try {
            $lesson = SmLesson::find($id);
            $lesson->delete();
            $SmLessonTopic = SmLessonTopic::where('lesson_id', $id)->get();
            if ($SmLessonTopic) {
                foreach ($SmLessonTopic as $t_data) {
                    SmLessonTopic::destroy($t_data->id);
                }
            }

            $SmLessonTopicDetail = SmLessonTopicDetail::where('lesson_id', $id)->get();
            if ($SmLessonTopicDetail) {
                foreach ($SmLessonTopicDetail as $td_data) {
                    SmLessonTopicDetail::destroy($td_data->id);
                }
            }

            $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
            if ($LessonPlanner) {
                foreach ($LessonPlanner as $lp_data) {
                    LessonPlanner::destroy($lp_data->id);
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function lessonPlanner()
    {
        return view('lesson::lesson.lesson_planner');
    }

    public function loadLesson()
    {
        $user = Auth::user();
        $teacher_info = SmStaff::where('user_id', $user->id)->first();
        $subjects = SmAssignSubject::select('subject_id')
            ->where('teacher_id', $teacher_info->id)->get();

        $data['subjects'] = SmSubject::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', $user->school_id)->get();
        $data['sections'] = SmSection::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', $user->school_id)->get();

        if ($user->role_id === 4) {
            if(moduleStatusCheck('University')){
                $data['lessons'] = SmLesson::query()->whereIn('un_subject_id', $subjects)->statusCheck()->groupBy(['class_id', 'section_id', 'un_subject_id']);
            }else{
                $data['lessons'] = SmLesson::query()->whereIn('subject_id', $subjects)->statusCheck()->groupBy(['class_id', 'section_id', 'subject_id']);
            }
        } else {
            $data['lessons'] = SmLesson::query()
                ->statusCheck();
        }

        if(moduleStatusCheck('University')){
            $data['lessons'] = $data['lessons']->groupBy(['un_department_id','un_faculty_id', 'un_section_id', 'un_subject_id']);
        } else{
            $query = SmLesson::with([
                'lessons',
                'class:id,class_name',
                'section:id,section_name',
                'subject'
            ]);

            if (shiftEnable()) {
                $query->with('shift');
            }

            $data['lessons'] = $query
                ->select('class_id', 'section_id', 'subject_id', 'lesson_title', 'active_status', 'id', 'shift_id')
                ->groupBy('class_id', 'section_id', 'subject_id', 'lesson_title', 'active_status', 'id', 'shift_id')
                ->get();
        }



        if (! teacherAccess()) {
            $data['classes'] = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $user->school_id)->select(['id', 'class_name'])->get();
        } else {
            $data['classes'] = SmAssignSubject::where('teacher_id', $teacher_info->id)
                ->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.class_id')
                ->where('sm_assign_subjects.academic_id', getAcademicId())
                ->where('sm_assign_subjects.active_status', 1)
                ->where('sm_assign_subjects.school_id', $user->school_id)
                ->select('sm_classes.id', 'class_name')
                ->groupBy('sm_classes.id')
                ->get();
        }

        return $data;
    }

    public function lessonListAjax(Request $request)
    {
        if (! $request->ajax()) {
            $subjects = $subjects ?? [];
            if (Auth::user()->role_id === 4) {
                $lessons = SmLesson::with('lessons', 'class', 'section', 'subject')
                    ->whereIn('subject_id', $subjects)->statusCheck()
                    ->get();
            } else {
                $lessons = SmLesson::with('lessons', 'class', 'section', 'subject')
                    ->statusCheck()
                    ->get();
            }

            return DataTables::of($lessons)
                ->addIndexColumn()
                ->addColumn('lesson_name', function ($row): string {
                    $lesson_name = '';
                    $lesson_title = SmLesson::lessonName($row->class_id, $row->section_id, $row->subject_id);
                    foreach ($lesson_title as $data) {
                        $lesson_name .= $data->lesson_title;
                        if ($lesson_title->last() !== $data) {
                            $lesson_name .= ',';
                        }
                    }

                    return $lesson_name;
                })
                ->addColumn('action', function ($row): string {
                    if (moduleStatusCheck('University')) {
                        return '<div class="dropdown CRM_dropdown">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>

                            <div class="dropdown-menu dropdown-menu-right">'.
                                (userPermission('un-lesson-edit') ? '<a class="dropdown-item" href="'.route('lesson-edit', [$row->un_session_id, $row->un_faculty_id ?? 0, $row->un_department_id, $row->un_academic_id, $row->un_semester_id, $row->un_semester_label_id, $row->un_subject_id ?? 0]).'">'.app('translator')->get('common.edit').'</a>' : '').
                                (userPermission('lesson-delete') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >'.app('translator')->get('common.disable').'</a></span>' :
                                    '<a onclick="deleteLesson('.$row->id.');"  class="dropdown-item" href="#" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                                '</div>
                        </div>';
                    }

                    return '<div class="dropdown CRM_dropdown">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>

                        <div class="dropdown-menu dropdown-menu-right">'.
                        (userPermission('lesson-edit') ? '<a class="dropdown-item" href="'.route('lesson-edit', [$row->class_id, $row->section_id, $row->subject_id]).'">'.app('translator')->get('common.edit').'</a>' : '').
                        (userPermission('lesson-delete') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >'.app('translator')->get('common.disable').'</a></span>' :
                        '<a onclick="deleteLesson('.$row->id.');"  class="dropdown-item" href="#" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                        '</div>
                      </div>';
                })
                ->rawColumns(['action', 'lesson_name'])
                ->make(true);
        }

        return null;
    }
}
