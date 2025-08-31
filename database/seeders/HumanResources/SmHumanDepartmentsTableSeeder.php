<?php

namespace Database\Seeders\HumanResources;

use App\SmHumanDepartment;
use Illuminate\Database\Seeder;

class SmHumanDepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, int $count = 10): void
    {
        SmHumanDepartment::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
