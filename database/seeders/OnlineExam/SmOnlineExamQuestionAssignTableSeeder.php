<?php

namespace Database\Seeders\OnlineExam;

use App\SmOnlineExam;
use App\SmOnlineExamQuestionAssign;
use App\SmQuestionBank;
use Illuminate\Database\Seeder;

class SmOnlineExamQuestionAssignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count): void
    {
        $online_exams = SmOnlineExam::where('school_id', $school_id)->where('academic_id', $academic_id)->take(10)->get();
        foreach ($online_exams as $online_exam) {
            $question_banks = SmQuestionBank::where('school_id', $school_id)->where('academic_id', $academic_id)->take(10)->get();
            foreach ($question_banks as $question_bank) {
                $store = new SmOnlineExamQuestionAssign();
                $store->online_exam_id = $online_exam->id;
                $store->question_bank_id = $question_bank->id;
                $store->created_at = date('Y-m-d h:i:s');
                $store->school_id = $school_id;
                $store->academic_id = $academic_id;
                $store->save();
            }

        }
    }
}
