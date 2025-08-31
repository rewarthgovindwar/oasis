<?php

namespace Database\Seeders\Exam;

use App\Models\StudentRecord;
use App\SmAssignSubject;
use App\SmClassSection;
use App\SmExam;
use App\SmExamMarksRegister;
use App\SmExamSetup;
use App\SmMarkStore;
use App\SmResultStore;
use App\YearCheck;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmExamMarksRegistersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $smExam = new SmExam();
        $smExam->exam_type_id = 1;
        $smExam->class_id = 1;
        $smExam->section_id = 1;
        $smExam->subject_id = 1;
        $smExam->exam_mark = 100;
        $smExam->created_by = 1;
        $smExam->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smExam->school_id = 1;
        $smExam->academic_id = 1;
        $smExam->save();
        $smExam->toArray();

        $ex_title = 'First Term Exam';
        $ex_mark = 100;
        $smExamSetup = new SmExamSetup();
        $smExamSetup->exam_id = $smExam->id;
        $smExamSetup->class_id = 1;
        $smExamSetup->section_id = 1;
        $smExamSetup->subject_id = 1;
        $smExamSetup->exam_term_id = 1;
        $smExamSetup->exam_title = $ex_title;
        $smExamSetup->exam_mark = $ex_mark;
        $smExamSetup->created_by = 1;
        $smExamSetup->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smExamSetup->school_id = 1;
        $smExamSetup->academic_id = 1;
        $smExamSetup->save();

        $smMarkStore = new SmMarkStore();
        $smMarkStore->exam_term_id = 1;
        $smMarkStore->class_id = 1;
        $smMarkStore->section_id = 1;
        $smMarkStore->subject_id = 1;
        $smMarkStore->student_id = 1;
        $smMarkStore->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smMarkStore->total_marks = 100;
        $smMarkStore->exam_setup_id = 1;
        $smMarkStore->student_record_id = 1;

        $smMarkStore->is_absent = 0;

        $smMarkStore->teacher_remarks = 'Good';

        $smMarkStore->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smMarkStore->school_id = 1;
        $smMarkStore->academic_id = 1;

        $smMarkStore->save();

        $smResultStore = new SmResultStore();
        $smResultStore->class_id = 1;
        $smResultStore->section_id = 1;
        $smResultStore->subject_id = 1;
        $smResultStore->exam_type_id = 1;
        $smResultStore->student_id = 1;
        $smResultStore->is_absent = 0;
        $smResultStore->total_marks = 100;
        $smResultStore->total_gpa_point = 5;
        $smResultStore->total_gpa_grade = 'A+';
        $smResultStore->teacher_remarks = 'Good';
        $smResultStore->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smResultStore->school_id = 1;
        $smResultStore->student_record_id = 1;
        $smResultStore->academic_id = 1;
        $smResultStore->save();

        $classSection = SmClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)->where('section_id', $classSection->section_id)->where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($students as $student) {
            $class_id = $student->class_id;
            $section_id = $student->section_id;
            $subjects = SmAssignSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->where('class_id', $class_id)->where('section_id', $section_id)->get();

            foreach ($subjects as $subject) {
                $store = new SmExamMarksRegister();
                $store->exam_id = 1;
                $store->student_id = $student->student_id;
                $store->subject_id = $subject->subject_id;
                $store->obtained_marks = random_int(40, 90);
                $store->exam_date = fake()->dateTime()->format('Y-m-d');
                $store->comments = fake()->realText($maxNbChars = 50, $indexSize = 2);
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
            } // end subject

        }

        // end student list
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
