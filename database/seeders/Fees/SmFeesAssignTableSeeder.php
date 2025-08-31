<?php

namespace Database\Seeders\Fees;

use App\Models\StudentRecord;
use App\SmClassSection;
use App\SmFeesAssign;
use App\SmFeesMaster;
use Illuminate\Database\Seeder;

class SmFeesAssignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        $classSection = SmClassSection::where('school_id', $school_id)->where('academic_id', $academic_id)->first();
        $students = StudentRecord::where('class_id', $classSection->class_id)
            ->where('section_id', $classSection->section_id)
            ->where('school_id', $school_id)
            ->where('academic_id', $academic_id)
            ->get();
        foreach ($students as $student) {
            $val = 1 + random_int(0, mt_getrandmax()) % 5;
            $fees_masters = SmFeesMaster::where('active_status', 1)
                ->where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->take($val)->get();
            foreach ($fees_masters as $fee_master) {
                $store = new SmFeesAssign();
                $store->student_id = $student->student_id;
                $store->record_id = $student->id;
                $store->fees_master_id = $fee_master->id;
                $store->school_id = $school_id;
                $store->academic_id = $academic_id;
                $store->save();
            }
        }
    }
}
