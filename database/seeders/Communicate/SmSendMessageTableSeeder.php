<?php

namespace Database\Seeders\Communicate;

use App\SmSendMessage;
use Illuminate\Database\Seeder;

class SmSendMessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 10): void
    {
        SmSendMessage::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ]);
    }
}
