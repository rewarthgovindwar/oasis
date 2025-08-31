<?php

namespace Database\Seeders\Accounts;

use App\SmChartOfAccount;
use Illuminate\Database\Seeder;

class SmChartOfAccountsTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // SmChartOfAccount::query()->truncate();
        $store = new SmChartOfAccount();
        $store->head = 'Donation';
        $store->type = 'I';
        $store->save();

        $store = new SmChartOfAccount();
        $store->head = 'Scholarship';
        $store->type = 'E';
        $store->save();

        $store = new SmChartOfAccount();
        $store->head = 'Product Sales';
        $store->type = 'I';
        $store->save();

        $store = new SmChartOfAccount();
        $store->head = 'Utility Bills';
        $store->type = 'E';
        $store->save();
    }
}
