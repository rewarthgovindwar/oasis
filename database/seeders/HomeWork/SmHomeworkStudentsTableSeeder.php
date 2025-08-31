<?php

namespace Database\Seeders\HomeWork;

use App\Models\StudentRecord;
use App\SmClass;
use App\SmHomework;
use App\SmHomeworkStudent;
use Illuminate\Database\Seeder;

class SmHomeworkStudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        SmClass::where('school_id', $school_id)->where('academic_id', $academic_id)->value('id');
        $students = StudentRecord::where('class_id', 1)->where('school_id', $school_id)->get();
        foreach ($students as $student) {
            $homeworks = SmHomework::where('class_id', $student->class_id)->where('school_id', 1)->get();
            foreach ($homeworks as $homework) {
                $s = new SmHomeworkStudent();
                $s->student_id = $student->student_id;
                // $s->student_record_id = $record->id;
                $s->homework_id = $homework->id;
                $s->marks = random_int(5, 10);
                $s->teacher_comments = fake()->text(100);
                $s->complete_status = 'C';
                $s->created_at = date('Y-m-d h:i:s');
                $s->school_id = $school_id;
                $s->academic_id = $academic_id;
                $s->save();
            }
        }
    }
}
