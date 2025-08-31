<?php

namespace Database\Seeders\Academics;

use App\SmSubject;
use Illuminate\Database\Seeder;

class SmSubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, $academic_id = 1, int $count = 10): void
    {
        SmSubject::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
