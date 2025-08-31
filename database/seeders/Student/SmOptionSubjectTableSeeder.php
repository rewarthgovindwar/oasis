<?php

namespace Database\Seeders\Student;

use App\Models\StudentRecord;
use App\SmClassSection;
use App\SmOptionalSubjectAssign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmOptionSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 1): void
    {
        $classSection = SmClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)
            ->latest()->first();

        $students = StudentRecord::where('class_id', $classSection->class_id)
            ->where('section_id', $classSection->section_id)
            ->where('school_id', $school_id)
            ->where('academic_id', $academic_id)
            ->get();
        if ($students) {
            $subjects = DB::table('sm_assign_subjects')->where('class_id', $classSection->class_id)->get();
            if (count($subjects) > 0) {
                foreach ($students as $student) {
                    $s = new SmOptionalSubjectAssign();
                    $s->student_id = $student->student_id;
                    $s->session_id = $student->session_id;
                    $s->subject_id = 1;
                    $s->school_id = $school_id;
                    $s->academic_id = $academic_id;
                    $s->save();
                }
            }
        }
    }
}
