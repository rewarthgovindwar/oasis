<?php

namespace Database\Seeders\Transport;

use App\SmVehicle;
use Illuminate\Database\Seeder;

class SmVehiclesTableSeeder extends Seeder
{
    public function run($school_id = 1, int $count = 5): void
    {

        SmVehicle::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
