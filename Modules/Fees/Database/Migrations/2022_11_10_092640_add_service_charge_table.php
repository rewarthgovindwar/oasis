<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceChargeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fm_fees_transactions', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_transactions', 'service_charge')) {
                $blueprint->float('service_charge')->nullable();
            }
        });
        Schema::table('fm_fees_invoice_chields', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_invoice_chields', 'service_charge')) {
                $blueprint->float('service_charge')->after('paid_amount')->nullable();
            }
        });
        Schema::table('fm_fees_transaction_chields', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_transaction_chields', 'service_charge')) {
                $blueprint->float('service_charge')->after('paid_amount')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('', function (Blueprint $blueprint): void {});
    }
}
