<?php

namespace Database\Seeders\Exam;

use App\SmAssignSubject;
use App\SmExamSchedule;
use App\SmExamType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SmExamSchedulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        $assign_subjects = SmAssignSubject::where(['school_id' => $school_id, 'academic_id' => $academic_id])->distinct(['class_id', 'section_id', 'subject_id'])->get();
        $sm_exams = SmExamType::where(['school_id' => $school_id, 'academic_id' => $academic_id])->get();
        $start_time = ['09:00:00', '10:30:00', '12:00:00', '14:00:00', '15:39:00'];
        $end_time = ['09:45:00', '11:15:00', '12:45:00', '14:45:00', '16:39:00'];
        foreach ($sm_exams as $sm_exam) {
            foreach ($assign_subjects as $key => $data) {
                $exam_routine = new SmExamSchedule;
                $exam_routine->exam_term_id = $sm_exam->id;
                $exam_routine->class_id = $data->class_id;
                $exam_routine->section_id = $data->section_id;
                $exam_routine->subject_id = $data->subject_id;
                $exam_routine->teacher_id = $data->teacher_id;
                $exam_routine->date = Carbon::now()->format('Y-m-d');
                $exam_routine->start_time = $start_time[$key] ?? '08:00:00';
                $exam_routine->end_time = $end_time[$key] ?? '08:45:00';
                $exam_routine->room_id = 1;
                $exam_routine->school_id = $school_id;
                $exam_routine->academic_id = $academic_id;
                $exam_routine->save();
            }
        }
    }
}
