<?php

namespace Database\Seeders\Exam;

use App\Models\StudentRecord;
use App\SmAssignSubject;
use App\SmExam;
use App\SmExamAttendance;
use App\SmExamAttendanceChild;
use App\YearCheck;
use Illuminate\Database\Seeder;

class SmExamAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id): void
    {
        $smExamTypes = SmExam::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        $assignSubjects = SmAssignSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($smExamTypes as $smExamType) {
            foreach ($assignSubjects as $assignSubject) {
                $studentRecord = StudentRecord::where('school_id', $school_id)->where('academic_id', $academic_id)->where('class_id', $assignSubject->class_id)->where('section_id', $assignSubject->section_id)->get();
                $store = new SmExamAttendance();
                $store->exam_id = $smExamType->id;
                $store->subject_id = $assignSubject->subject_id;
                $store->class_id = $assignSubject->class_id;
                $store->section_id = $assignSubject->section_id;
                $store->created_by = 1;
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
                foreach ($studentRecord as $record) {
                    $exam_attendance_child = new SmExamAttendanceChild();
                    $exam_attendance_child->exam_attendance_id = $store->id;
                    $exam_attendance_child->student_id = $record->student_id;
                    $exam_attendance_child->student_record_id = $record->id;
                    $exam_attendance_child->class_id = $record->class_id;
                    $exam_attendance_child->section_id = $record->section_id;
                    $exam_attendance_child->attendance_type = 'P';
                    $exam_attendance_child->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                    $exam_attendance_child->school_id = $school_id;
                    $exam_attendance_child->academic_id = $academic_id;
                    $exam_attendance_child->save();
                }
            }

        }
    }
}
