<?php

namespace Database\Seeders\HomeWork;

use Illuminate\Database\Seeder;

class SmStudentHomeworkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $subject_list = SmAssignSubject::all();
        foreach ($subject_list as $subject) {
            $store = new SmHomework();
            $store->class_id = $subject->class_id;
            $store->section_id = $subject->section_id;
            $store->subject_id = $subject->subject_id;
            $store->homework_date = fake()->dateTime()->format('Y-m-d');
            $store->submission_date = fake()->dateTime()->format('Y-m-d');
            $store->description = fake()->text(500);
            $store->marks = 10;
            $store->file = '';
            $store->created_by = 1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }

    }
}
