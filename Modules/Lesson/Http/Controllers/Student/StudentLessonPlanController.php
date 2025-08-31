<?php

namespace Modules\Lesson\Http\Controllers\Student;

use App\ApiBaseMethod;
use App\SmClass;
use App\SmClassTime;
use App\SmStudent;
use App\SmWeekend;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class StudentLessonPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request, $id = null)
    {
        try {

            $this_week = date('W');

            $week_start_id = generalSetting()->week_start_id;
            $week_end = SmWeekend::where('id', $week_start_id)->value('name');
            $start_day = WEEK_DAYS_BY_NAME[$week_end ?? 'Monday'];

            $startDate = Carbon::now()->startOfWeek($start_day);
            $endDate = Carbon::now()->endOfWeek($start_day + 6);

            $period = CarbonPeriod::create($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

            $dates = [];
            foreach ($period as $date) {
                $dates[] = $date->format('Y-m-d');
            }

            $student_detail = SmStudent::where('user_id', auth()->user()->id)->first();
            // return $student_detail;
            $class_id = $student_detail->class_id;
            $section_id = $student_detail->section_id;

            $records = studentRecords(null, $student_detail->id)->get();

            // $sm_weekends = SmWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id',Auth::user()->school_id)->get();
            $sm_weekends = SmWeekend::where('school_id', Auth::user()->school_id)
                ->where('active_status', 1)
                ->get();

            $orderedWeekends = [];
            foreach ($sm_weekends as $sm_weekend) {
                $dayIndex = WEEK_DAYS_BY_NAME[$sm_weekend->name] ?? null;
                if ($dayIndex !== null) {
                    $orderedWeekends[$dayIndex] = $sm_weekend;
                }
            }

            ksort($orderedWeekends);

            $sm_weekends = array_merge(
                array_slice($orderedWeekends, $start_day),
                array_slice($orderedWeekends, 0, $start_day)
            );

            return view('lesson::student.student_lesson_plan', ['dates' => $dates, 'this_week' => $this_week, 'class_id' => $class_id, 'section_id' => $section_id, 'sm_weekends' => $sm_weekends, 'records' => $records]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        // return view('lesson::index');
    }

    public function overview(Request $request, $id = null)
    {

        try {

            $login_id = ApiBaseMethod::checkUrl($request->fullUrl()) ? $id : Auth::user()->id;

            $student_detail = SmStudent::where('user_id', $login_id)->first();
            $class = $student_detail->class_id;
            $section = $student_detail->section_id;
            $academic_id = $student_detail->academic_id;
            $school_id = $student_detail->school_id;
            $records = studentRecords(null, $student_detail->id)->get();
            $classes = SmClass::get();

            return view('lesson::student.student_lesson_plan_overview', ['classes' => $classes, 'records' => $records]);

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function changeWeek(Request $request, $next_date)
    {
        $start_date = Carbon::parse($next_date)->addDay();
        $date = Carbon::parse($next_date)->addDay();
        $end_date = Carbon::parse($start_date)->addDay();
        $this_week = $end_date->weekOfYear;
        $carbonPeriod = CarbonPeriod::create($start_date, $end_date);
        $dates = [];
        foreach ($carbonPeriod as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            $user_id = $user ? $user->id : $request->user_id;
        }

        $student_detail = SmStudent::where('user_id', $user_id)->first();
        // return $student_detail;
        $class_id = $student_detail->class_id;
        $section_id = $student_detail->section_id;

        $sm_weekends = SmWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

        $class_times = SmClassTime::where('type', 'class')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        $records = studentRecords(null, $student_detail->id)->get();

        return view('lesson::student.student_lesson_plan', ['dates' => $dates, 'this_week' => $this_week, 'class_times' => $class_times, 'class_id' => $class_id, 'section_id' => $section_id, 'sm_weekends' => $sm_weekends, 'records' => $records]);

    }

    public function discreaseChangeWeek(Request $request, $pre_date)
    {

        $end_date = Carbon::parse($pre_date)->subDays(1);

        $start_date = Carbon::parse($end_date)->subDays(6);

        $this_week = $end_date->weekOfYear;

        $carbonPeriod = CarbonPeriod::create($start_date, $end_date);

        $dates = [];
        foreach ($carbonPeriod as $date) {
            $dates[] = $date->format('Y-m-d');

        }

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $user_id = $id;
        } else {
            $user = Auth::user();

            $user_id = $user ? $user->id : $request->user_id;
        }

        $student_detail = SmStudent::where('user_id', $user_id)->first();
        // return $student_detail;
        $class_id = $student_detail->class_id;
        $section_id = $student_detail->section_id;

        $sm_weekends = SmWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

        $class_times = SmClassTime::where('type', 'class')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

        $records = studentRecords(null, $student_detail->id)->get();

        return view('lesson::student.student_lesson_plan', ['dates' => $dates, 'this_week' => $this_week, 'class_times' => $class_times, 'class_id' => $class_id, 'section_id' => $section_id, 'sm_weekends' => $sm_weekends, 'records' => $records]);
    }

    public function create()
    {
        return view('lesson::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): void
    {
        //
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('lesson::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('lesson::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id): void
    {
        //
    }
}
