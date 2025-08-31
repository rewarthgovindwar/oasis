<?php

namespace Database\Seeders\Admin;

use App\SmPostalDispatch;
use Illuminate\Database\Seeder;

class SmPostalDispatchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 10): void
    {
        SmPostalDispatch::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
