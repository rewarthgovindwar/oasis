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
        if (Schema::hasColumn('sm_student_id_cards', 'title')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('title', 30)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'page_layout_style')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('page_layout_style', 30)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'user_photo_style')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('user_photo_style', 30)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'user_photo_width')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('user_photo_width', 30)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'admission_no')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('admission_no', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'student_name')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('student_name', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'class')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('class', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'father_name')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('father_name', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'mother_name')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('mother_name', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'student_address')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('student_address', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'phone_number')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('phone_number', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'blood')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('blood', 10)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'dob')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->string('dob', 10)->nullable()->change();
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
