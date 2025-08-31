<?php

namespace Database\Seeders\Accounts;

use App\SmBankAccount;
use Illuminate\Database\Seeder;

class SmBankAccountsTableSeeder extends Seeder
{
    public function run($school_id = 1, int $count = 10): void
    {
        SmBankAccount::factory()->times($count)->create([
            'school_id' => $school_id,
        ]);
    }
}
