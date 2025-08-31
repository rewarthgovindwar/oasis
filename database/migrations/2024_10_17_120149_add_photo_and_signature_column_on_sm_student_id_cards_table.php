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
        if (! Schema::hasColumn('sm_student_id_cards', 'photo')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->integer('photo')->default(1)->after('blood');
            });
        }

        if (! Schema::hasColumn('sm_student_id_cards', 'signature_status')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->integer('signature_status')->default(1)->after('photo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sm_student_id_cards', 'photo')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->dropColumn('photo');
            });
        }

        if (Schema::hasColumn('sm_student_id_cards', 'signature_status')) {
            Schema::table('sm_student_id_cards', function ($table): void {
                $table->dropColumn('signature_status');
            });
        }
    }
};
