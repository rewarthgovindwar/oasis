<?php

namespace Database\Seeders\Accounts;

use App\SmAddIncome;
use App\SmIncomeHead;
use Illuminate\Database\Seeder;

class SmIncomeHeadsTableSeeder extends Seeder
{
    public function run($school_id = 1, int $count = 10): void
    {
        SmIncomeHead::factory()->times($count)->create([
            'school_id' => $school_id,
        ])->each(function ($income_head): void {
            SmAddIncome::factory()->times(10)->create([
                'school_id' => $income_head->school_id,
                'income_head_id' => $income_head->id,
            ]);
        });
    }
}
