<?php

namespace Database\Seeders\Dormitory;

use App\SmRoomList;
use Illuminate\Database\Seeder;

class SmRoomListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, int $count = 5): void
    {
        SmRoomList::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
