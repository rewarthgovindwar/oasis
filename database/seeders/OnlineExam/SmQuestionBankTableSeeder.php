<?php

namespace Database\Seeders\OnlineExam;

use App\SmAssignSubject;
use App\SmQuestionBank;
use App\SmQuestionGroup;
use Illuminate\Database\Seeder;

class SmQuestionBankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {

        $group_id = SmQuestionGroup::where('school_id', $school_id)->where('academic_id', $academic_id)->value('id');
        $question_details = SmAssignSubject::all();
        foreach ($question_details as $question_detail) {

            $store = new SmQuestionBank();
            $store->q_group_id = $group_id;
            $store->class_id = $question_detail->class_id;
            $store->section_id = $question_detail->section_id;
            $store->type = 'M';
            $store->question = fake()->realText($maxNbChars = 80, $indexSize = 1);
            $store->marks = 100;
            $store->trueFalse = 'T';
            $store->suitable_words = fake()->realText($maxNbChars = 50, $indexSize = 1);
            $store->number_of_option = 4;
            $store->created_at = date('Y-m-d h:i:s');
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }

    }
}
