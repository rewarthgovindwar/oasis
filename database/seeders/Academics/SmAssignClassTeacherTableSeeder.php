<?php

namespace Database\Seeders\Academics;

use App\SmAssignClassTeacher;
use App\SmClassTeacher;
use App\SmStaff;
use Illuminate\Database\Seeder;

class SmAssignClassTeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, $academic_id = null, $count = 5): void
    {
        $teacher_id = SmStaff::where('role_id', 4)->where('school_id', $school_id)->first()->id;
        $SmAssignClassTeachers = SmAssignClassTeacher::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($SmAssignClassTeachers as $SmAssignClassTeacher) {
            $store = new SmClassTeacher();
            $store->assign_class_teacher_id = $SmAssignClassTeacher->id;
            $store->teacher_id = $teacher_id;
            $store->created_at = date('Y-m-d h:i:s');
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }
    }
}
