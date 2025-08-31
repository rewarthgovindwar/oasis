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
        if (Schema::hasColumn('fm_fees_types', 'name')) {
            Schema::table('fm_fees_types', function ($table): void {
                $table->string('name', 40)->nullable()->change();
            });
        }

        if (Schema::hasColumn('fm_fees_types', 'type')) {
            Schema::table('fm_fees_types', function ($table): void {
                $table->string('type', 20)->default('fees')->comment('fees, lms')->change();
            });
        }


            Schema::table('incidents', function ($table): void {
                if (Schema::hasColumn($table->getTable(), 'type')) {
                    $table->double('point', 20)->default(0)->change();
                }
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
