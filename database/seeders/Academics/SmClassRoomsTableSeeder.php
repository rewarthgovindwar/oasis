<?php

namespace Database\Seeders\Academics;

use App\SmClassRoom;
use Illuminate\Database\Seeder;

class SmClassRoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        SmClassRoom::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);

    }
}
