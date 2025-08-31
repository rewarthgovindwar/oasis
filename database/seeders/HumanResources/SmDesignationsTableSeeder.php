<?php

namespace Database\Seeders\HumanResources;

use App\SmDesignation;
use Illuminate\Database\Seeder;

class SmDesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, int $count = 1): void
    {
        SmDesignation::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
