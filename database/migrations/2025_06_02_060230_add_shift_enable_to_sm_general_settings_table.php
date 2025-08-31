<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sm_general_settings', function (Blueprint $table) {
            $table->tinyInteger('shift_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sm_general_settings', function (Blueprint $table) {
            $table->dropColumn('shift_enable');
        });
    }
};
