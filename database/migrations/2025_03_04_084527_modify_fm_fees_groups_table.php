<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('fm_fees_groups', 'name')) {
            Schema::table('fm_fees_groups', function ($table): void {
                $table->string('name', 40)->change();
            });
        }

        if (Schema::hasColumn('fm_fees_transactions', 'invoice_number')) {
            Schema::table('fm_fees_transactions', function ($table): void {
                $table->string('invoice_number', 50)->change();
            });
        }

        if (Schema::hasColumn('fm_fees_transactions', 'payment_method')) {
            Schema::table('fm_fees_transactions', function ($table): void {
                $table->string('payment_method', 80)->change();
            });
        }

        if (Schema::hasColumn('fm_fees_transactions', 'paid_status')) {
            Schema::table('fm_fees_transactions', function ($table): void {
                $table->string('paid_status', 30)->change();
            });
        }

        if (Schema::hasColumn('fm_fees_transactions', 'payment_note')) {
            Schema::table('fm_fees_transactions', function ($table): void {
                $table->string('payment_note', 100)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
