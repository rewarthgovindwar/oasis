<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Shift;
use App\SmClassSection;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\Http\Requests\ShiftRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ShiftController extends Controller
{
    public function index()
    {
        $this->updateDatabase();

        $data['title'] = __('admin.shifts');
        $data['shifts'] = Shift::where('school_id', auth()->user()->school_id)->get();

        return view('backEnd.shift.index', $data);
    }

    public static function updateDatabase(){
        try {
            $tables_array = [
                'student_records',
                'sm_assign_class_teachers',
                'chat_groups',
                'sm_class_teachers',
                'sm_assign_subjects',
                'sm_class_routine_updates',
                'sm_teacher_upload_contents',
                'sm_lessons',
                'lesson_planners',
                'sm_lesson_topics',
                'video_uploads',
                'sm_homeworks',
                'sm_exams',
                'sm_exam_setups',
                'sm_exam_schedules',
                'sm_classes',
                'sm_class_sections',
                'sm_exam_attendances',
                'sm_exam_attendance_children',
                'sm_mark_stores',
                'sm_result_stores',
                'sm_online_exams',
                'sm_temporary_meritlists',
                'sm_student_attendances',
                'exam_merit_positions',
                'sm_question_banks',
                'all_exam_wise_positions',
                'sm_bank_payment_slips',
                'fm_fees_invoices',
                'sm_admission_queries',
            ];
    
            foreach ($tables_array as $key => $table_name) {
                if (Schema::hasTable($table_name) && !Schema::hasColumn($table_name, 'shift_id')) {
                    Schema::table($table_name, function (Blueprint $table) {
                        $table->integer('shift_id')->nullable();
                    });
                }
            }

            Schema::table('all_exam_wise_positions', function (Blueprint $table) {
                $table->string('grade')->nullable()->change();
            });
    
            //sm_student_promotions
            if (Schema::hasTable('sm_student_promotions')) {
                if (!Schema::hasColumn('sm_student_promotions', 'previous_shift_id')) {
                    Schema::table('sm_student_promotions', function (Blueprint $table) {
                        $table->integer('previous_shift_id')->nullable();
                    });
                }
    
                if (!Schema::hasColumn('sm_student_promotions', 'current_shift_id')) {
                    Schema::table('sm_student_promotions', function (Blueprint $table) {
                        $table->integer('current_shift_id')->nullable();
                    });
                }
            }

            // $shift_menus=array(
            //     'shift.index' => array(
            //         'module' => null,
            //         'sidebar_menu' => 'academics',
            //         'name' => 'Shift',
            //         'lang_name' => 'admin.shift',
            //         'icon' => null,
            //         'svg' => null,
            //         'route' => 'shift.index',
            //         'parent_route' => 'academics',
            //         'is_admin' => 1,
            //         'is_teacher' => 0,
            //         'is_student' => 0,
            //         'is_parent' => 0,
            //         'position' => 2,
            //         'is_saas' => 0,
            //         'is_menu' => 1,
            //         'status' => 1,
            //         'menu_status' => 1,
            //         'relate_to_child' => 0,
            //         'alternate_module' => null,
            //         'permission_section' => 0,
            //         'user_id' => null,
            //         'type' => 2,
            //         'old_id' => 265,
            //         'child' => array(
            //             'shift.store' => array(
            //                 'module' => null,
            //                 'sidebar_menu' => null,
            //                 'name' => 'Add',
            //                 'lang_name' => null,
            //                 'icon' => null,
            //                 'svg' => null,
            //                 'route' => 'shift.store',
            //                 'parent_route' => 'shift.index',
            //                 'is_admin' => 1,
            //                 'is_teacher' => 0,
            //                 'is_student' => 0,
            //                 'is_parent' => 0,
            //                 'position' => 267,
            //                 'is_saas' => 0,
            //                 'is_menu' => 0,
            //                 'status' => 1,
            //                 'menu_status' => 1,
            //                 'relate_to_child' => 0,
            //                 'alternate_module' => null,
            //                 'permission_section' => 0,
            //                 'user_id' => null,
            //                 'type' => 3,
            //                 'old_id' => 266,
            //             ),
            //             'shift.edit' => array(
            //                 'module' => null,
            //                 'sidebar_menu' => null,
            //                 'name' => 'Edit',
            //                 'lang_name' => null,
            //                 'icon' => null,
            //                 'svg' => null,
            //                 'route' => 'shift.edit',
            //                 'parent_route' => 'shift.index',
            //                 'is_admin' => 1,
            //                 'is_teacher' => 0,
            //                 'is_student' => 0,
            //                 'is_parent' => 0,
            //                 'position' => 268,
            //                 'is_saas' => 0,
            //                 'is_menu' => 0,
            //                 'status' => 1,
            //                 'menu_status' => 1,
            //                 'relate_to_child' => 0,
            //                 'alternate_module' => null,
            //                 'permission_section' => 0,
            //                 'user_id' => null,
            //                 'type' => 3,
            //                 'old_id' => 267,
            //             ),
            //             'shift.delete' => array(
            //                 'module' => null,
            //                 'sidebar_menu' => null,
            //                 'name' => 'Delete',
            //                 'lang_name' => null,
            //                 'icon' => null,
            //                 'svg' => null,
            //                 'route' => 'shift.delete',
            //                 'parent_route' => 'shift.index',
            //                 'is_admin' => 1,
            //                 'is_teacher' => 0,
            //                 'is_student' => 0,
            //                 'is_parent' => 0,
            //                 'position' => 269,
            //                 'is_saas' => 0,
            //                 'is_menu' => 0,
            //                 'status' => 1,
            //                 'menu_status' => 1,
            //                 'relate_to_child' => 0,
            //                 'alternate_module' => null,
            //                 'permission_section' => 0,
            //                 'user_id' => null,
            //                 'type' => 3,
            //                 'old_id' => 268,
            //             ),
            //         ),
            //     ),
            // );
            // foreach ($shift_menus as $data) {
            //     storePermissionData($data);
            // }



        } catch (\Throwable $th) {
            dd($th);
        }
    }
    public function store(ShiftRequest $request)
    {

        try {
            $shift = new Shift();
            $shift->name = $request->name;
            $shift->start_time = $request->start_time;
            $shift->end_time = $request->end_time;
            $shift->description = $request->description;
            $shift->school_id = auth()->user()->school_id;
            $shift->academic_id = getAcademicId();
            $shift->save();

            Toastr::success('Shift Addedd Successfully', 'Success');
            return redirect()->route('shift.index');
        } catch (\Throwable $th) {
            Toastr::error('Something Went Wrong', 'Error');
            return redirect()->route('shift.index');
        }
    }
    public function update(ShiftRequest $request)
    {

        try {
            $shift =Shift::find($request->id);
            $shift->name = $request->name;
            $shift->start_time = $request->start_time;
            $shift->end_time = $request->end_time;
            $shift->description = $request->description;
            $shift->school_id = auth()->user()->school_id;
            $shift->academic_id = getAcademicId();
            $shift->save();

            Toastr::success('Shift Updated Successfully', 'Success');
            return redirect()->route('shift.index');
        } catch (\Throwable $th) {
            Toastr::error('Something Went Wrong', 'Error');
            return redirect()->route('shift.index');
        }
    }
    public function edit($id)
    {
        $data['title'] = __('admin.shifts');
        $data['shifts'] = Shift::where('school_id', auth()->user()->school_id)->get();
        $data['editData'] = Shift::find($id);

        return view('backEnd.shift.index', $data);
    }

    public function status_change(Request $request)
    {
        try {
            $shift = Shift::find($request->id);
            if ($shift->active_status == 1) {
                $shift->active_status = 0;
            } else {
                $shift->active_status = 1;
            }
            $shift->save();
            return response()->json(['success' => true, 'message' => 'Status Change Successfully']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Something Went Wrong']);
        }
    }

    public function delete(Request $request)
    {
        try {
            $shift = Shift::find($request->id);
            $shift->delete();
            Toastr::success('Shift Deleted Successfully', 'Success');
            return redirect()->route('shift.index');
        } catch (\Throwable $th) {
            Toastr::error('Something Went Wrong', 'Error');
            return redirect()->route('shift.index');
        }
    }
    public function classesForShift(Request $request)
    {
        try {
            $classes=SmClassSection::with('className')->where('shift_id',$request->shift_id)->get();

            $classArray=[];
            foreach ($classes as $key => $class) {
                $classArray[$key]['id']=$class->className->id;
                $classArray[$key]['name']=$class->className->class_name;
            }
            $classArray = array_unique($classArray, SORT_REGULAR);

            return response()->json(['success' => true, 'data' => $classArray]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Something Went Wrong']);
        }
    }
    public function shifts_for_class(Request $request)
    {
        $class_section=SmClassSection::with('shift')->where('class_id',$request->class_id)
        ->where('school_id',auth()->user()->school_id)->first()->shift;
        return response()->json($class_section);
    }

    public function setting()
    {

        try {
            return view('backEnd.shift.setting');
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function postSetting(Request $request)
    {
        try {
            $general_settings = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
            $general_settings->shift_enable = $request->shift_enable;
            $general_settings->save();
            session()->forget('generalSetting');

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

}
