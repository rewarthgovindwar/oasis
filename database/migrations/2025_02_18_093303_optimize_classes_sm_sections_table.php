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
        if (Schema::hasColumn('sm_classes', 'class_name')) {
            Schema::table('sm_classes', function ($table): void {
                $table->string('class_name', 15)->change();
            });
        }

        if (Schema::hasColumn('sm_sections', 'section_name')) {
            Schema::table('sm_sections', function ($table): void {
                $table->string('section_name', 15)->change();
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
