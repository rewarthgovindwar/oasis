<?php

namespace Database\Seeders\Fees;

use App\Models\StudentRecord;
use App\SmClassSection;
use App\SmFeesPayment;
use App\SmFeesType;
use Illuminate\Database\Seeder;

class SmFeesPaymentTableSeeder extends Seeder
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
            $fees_types = SmFeesType::where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->where('active_status', 1)
                ->get();
            foreach ($fees_types as $fee_type) {
                $store = new SmFeesPayment();
                $store->student_id = $student->student_id;
                $store->record_id = $student->id;
                $store->fees_type_id = $fee_type->id;
                $store->fees_discount_id = 1;
                $store->discount_month = date('m');
                $store->discount_amount = 100;
                $store->fine = 50;
                $store->amount = 250;
                $store->payment_mode = 'C';
                $store->school_id = $school_id;
                $store->academic_id = $academic_id;
                $store->save();

            }
        }
    }
}
