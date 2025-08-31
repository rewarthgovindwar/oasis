<?php

namespace Database\Seeders\Admin;

use App\SmPostalReceive;
use Illuminate\Database\Seeder;

class SmPostalReceiveTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 10): void
    {
        SmPostalReceive::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
