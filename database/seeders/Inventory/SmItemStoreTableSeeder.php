<?php

namespace Database\Seeders\Inventory;

use App\SmItemStore;
use Illuminate\Database\Seeder;

class SmItemStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        SmItemStore::factory()->times($count)->create($school_academic);

    }
}
