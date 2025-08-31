<?php

namespace Database\Seeders\HumanResources;

use Database\Factories\SmHourRateFactory;
use Illuminate\Database\Seeder;

class SmHourRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, $count = 5): void
    {
        SmHourRateFactory::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);

    }
}
