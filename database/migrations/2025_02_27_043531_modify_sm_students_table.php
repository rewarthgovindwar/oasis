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
        if (Schema::hasColumn('sm_students', 'first_name')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('first_name', 70)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'last_name')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('last_name', 70)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'full_name')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('full_name', 130)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'caste')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('caste', 50)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'email')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('email', 50)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'mobile')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('mobile', 20)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'age')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('age', 20)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'height')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('height', 20)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'current_address')) {
            Schema::table('sm_students', function ($table): void {
                $table->text('current_address', 300)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'permanent_address')) {
            Schema::table('sm_students', function ($table): void {
                $table->text('permanent_address', 300)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'driver_id')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('driver_id', 25)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'national_id_no')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('national_id_no', 25)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'national_id_no')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('national_id_no', 25)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'local_id_no')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('local_id_no', 25)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'bank_account_no')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('bank_account_no', 30)->nullable()->change();
            });
        }

        if (Schema::hasColumn('sm_students', 'bank_name')) {
            Schema::table('sm_students', function ($table): void {
                $table->string('bank_name', 25)->nullable()->change();
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
