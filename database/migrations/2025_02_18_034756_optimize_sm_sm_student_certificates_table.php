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
        if (Schema::hasColumn('sm_student_certificates', 'name')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('name', 60)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'header_left_text')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('header_left_text', 90)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'type')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('type', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'footer_left_text')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('footer_left_text', 90)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'footer_center_text')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('footer_center_text', 90)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'body_font_family')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('body_font_family', 15)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'body_font_size')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('body_font_size', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_certificates', 'body_font_size')) {
            Schema::table('sm_student_certificates', function ($table): void {
                $table->string('body_font_size', 10)->nullable()->change();
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
