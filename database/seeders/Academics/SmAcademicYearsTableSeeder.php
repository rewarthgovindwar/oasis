<?php

namespace Database\Seeders\Academics;

use App\SmAcademicYear;
use Illuminate\Database\Seeder;

class SmAcademicYearsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, int $count = 10): void
    {
        SmAcademicYear::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
