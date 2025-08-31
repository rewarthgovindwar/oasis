<?php

namespace Database\Seeders\Admin;

use App\SmComplaint;
use App\SmSetupAdmin;
use Illuminate\Database\Seeder;

class SmComplaintsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        SmSetupAdmin::factory()->times($count)->create([
            'type' => 2,
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ])->each(function ($complaint_type) use ($count): void {
            SmComplaint::factory()->times($count)->create([
                'school_id' => $complaint_type->school_id,
                'academic_id' => $complaint_type->academic_id,
            ]);
        });

    }
}
