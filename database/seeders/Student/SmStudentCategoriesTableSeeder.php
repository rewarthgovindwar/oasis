<?php

namespace Database\Seeders\Student;

use App\SmStudentCategory;
use Illuminate\Database\Seeder;

class SmStudentCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, int $count = 6): void
    {
        SmStudentCategory::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
