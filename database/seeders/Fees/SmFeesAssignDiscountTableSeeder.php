<?php

namespace Database\Seeders\Fees;

use App\Models\StudentRecord;
use App\SmClassSection;
use App\SmFeesAssignDiscount;
use App\SmFeesDiscount;
use Illuminate\Database\Seeder;

class SmFeesAssignDiscountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        $classSection = SmClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)->where('section_id', $classSection->section_id)->where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        $feesDisCountId = SmFeesDiscount::where('school_id', $school_id)->where('academic_id', $academic_id)->value('id');
        foreach ($students as $student) {
            $store = new SmFeesAssignDiscount();
            $store->fees_discount_id = $feesDisCountId;
            $store->student_id = $student->student_id;
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }
    }
}
