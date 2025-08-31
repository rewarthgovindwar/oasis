<?php

use App\InfixModuleManager;
use Illuminate\Database\Migrations\Migration;

class RemoveXenditPaymentFromDefaultModule extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $xenditPayment = InfixModuleManager::where('name', 'XenditPayment')->first();
        if ($xenditPayment) {
            $xenditPayment->is_default = 0;
            $xenditPayment->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $xenditPayment = InfixModuleManager::where('name', 'XenditPayment')->first();
        if ($xenditPayment) {
            $xenditPayment->is_default = 1;
            $xenditPayment->save();
        }
    }
}
