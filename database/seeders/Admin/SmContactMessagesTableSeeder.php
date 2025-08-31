<?php

namespace Database\Seeders\Admin;

use App\SmContactMessage;
use Illuminate\Database\Seeder;

class SmContactMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, int $count = 5): void
    {
        SmContactMessage::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);

    }
}
