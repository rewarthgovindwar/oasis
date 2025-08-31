<?php

namespace Database\Seeders\Academics;

use App\SmAssignSubject;
use App\SmClassRoutineUpdate;
use App\SmWeekend;
use Illuminate\Database\Seeder;

class SmClassRoutineUpdatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 1): void
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        $classSectionSubjects = SmAssignSubject::where('school_id', $school_id)
            ->where('academic_id', $academic_id)
            ->get();
        $weekends = SmWeekend::where('school_id', $school_id)->get();
        foreach ($weekends as $weekend) {
            foreach ($classSectionSubjects as $classSectionSubject) {
                SmClassRoutineUpdate::factory()->times($count)->create(array_merge([
                    'day' => $weekend->id,
                    'class_id' => $classSectionSubject->class_id,
                    'section_id' => $classSectionSubject->section_id,
                    'subject_id' => $classSectionSubject->subject_id,
                    'teacher_id' => $classSectionSubject->teacher_id,
                ], $school_academic));
            }
        }

    }
}
