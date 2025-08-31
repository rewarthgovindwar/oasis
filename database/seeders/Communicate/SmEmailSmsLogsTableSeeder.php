<?php

namespace Database\Seeders\Communicate;

use App\SmEmailSmsLog;
use Illuminate\Database\Seeder;

class SmEmailSmsLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {

        SmEmailSmsLog::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);

    }
}
