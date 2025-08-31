<?php

namespace Database\Seeders\OnlineExam;

use App\SmAssignSubject;
use App\SmOnlineExam;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SmOnlineExamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        $question_details = SmAssignSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        foreach ($question_details as $question_detail) {
            $store = new SmOnlineExam();
            $store->subject_id = $question_detail->subject_id;
            $store->class_id = $question_detail->class_id;
            $store->section_id = $question_detail->section_id;
            $store->title = fake()->realText($maxNbChars = 30, $indexSize = 1);
            $store->date = date('Y-m-d');
            // $store->start_time = '10:00 AM';
            // $store->end_time = '11:00 AM';
            $store->start_time = Carbon::now()->setTimezone('Asia/Dhaka')->format('h:i A');
            $store->end_time = Carbon::now()->setTimezone('Asia/Dhaka')->addHours(3)->format('h:i A');
            $store->end_date_time = date('Y-m-d').' 11:00 AM';
            $store->percentage = 50;
            $store->instruction = fake()->realText($maxNbChars = 100, $indexSize = 1);
            $store->status = 1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }
    }
}
