<?php

use App\SmPaymentMethhod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWmWalletSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // User Table Add New Column
        $columnName = 'wallet_balance';
        if (! Schema::hasColumn('users', $columnName)) {
            Schema::table('users', function ($table): void {
                $table->float('wallet_balance')->default(0);
            });
        }

        // Add New Payment Method In Payment Method Settings

        $schools = moduleStatusCheck('Saas') ? App\SmSchool::all() : App\SmSchool::where('id', 1)->get();

        foreach ($schools as $school) {
            $payment_method = SmPaymentMethhod::where('method', 'Wallet')->where('school_id', $school->id)->first();
            if (! $payment_method) {
                $storePaymentMethod = new SmPaymentMethhod();
                $storePaymentMethod->method = 'Wallet';
                $storePaymentMethod->type = 'System';
                $storePaymentMethod->active_status = 1;
                $storePaymentMethod->created_by = 1;
                $storePaymentMethod->updated_by = 1;
                $storePaymentMethod->school_id = $school->id;
                $storePaymentMethod->save();
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wm__wallet_settings');
    }
}
