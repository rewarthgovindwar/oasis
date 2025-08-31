<?php

namespace Database\Seeders\Dormitory;

use App\SmDormitoryList;
use Illuminate\Database\Seeder;

class SmDormitoryListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, int $count = 5): void
    {
        SmDormitoryList::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
