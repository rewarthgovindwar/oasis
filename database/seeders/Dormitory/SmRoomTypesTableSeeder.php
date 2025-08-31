<?php

namespace Database\Seeders\Dormitory;

use App\SmRoomType;
use Illuminate\Database\Seeder;

class SmRoomTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, int $count = 5): void
    {
        SmRoomType::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
