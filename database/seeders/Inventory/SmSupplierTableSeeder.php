<?php

namespace Database\Seeders\Inventory;

use App\SmSupplier;
use Illuminate\Database\Seeder;

class SmSupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        //
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        SmSupplier::factory()->times($count)->create($school_academic);

    }
}
