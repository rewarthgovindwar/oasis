<?php

namespace Database\Seeders\Academics;

use App\SmSection;
use Illuminate\Database\Seeder;

class SmSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, $academic_id = null, int $count = 5): void
    {
        SmSection::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
