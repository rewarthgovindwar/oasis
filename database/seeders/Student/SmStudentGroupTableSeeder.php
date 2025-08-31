<?php

namespace Database\Seeders\Student;

use App\SmStudentGroup;
use Illuminate\Database\Seeder;

class SmStudentGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        SmStudentGroup::factory()->times($count)->create($school_academic);
    }
}
