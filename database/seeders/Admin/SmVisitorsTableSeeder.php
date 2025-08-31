<?php

namespace Database\Seeders\Admin;

use App\SmVisitor;
use Illuminate\Database\Seeder;

class SmVisitorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, int $count = 10): void
    {
        SmVisitor::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
