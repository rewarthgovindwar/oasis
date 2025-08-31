<?php

namespace Database\Seeders\Fees;

use App\Models\StudentRecord;
use App\SmClassSection;
use App\SmFeesCarryForward;
use Illuminate\Database\Seeder;

class SmFeesCarryForwardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        $classSection = SmClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)->where('section_id', $classSection->section_id)->where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($students as $student) {
            $store = new SmFeesCarryForward();
            $store->student_id = $student->student_id;
            $store->balance = random_int(1000, 5000);
            $store->save();
        }
    }
}
