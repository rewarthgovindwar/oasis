<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn($blueprint->getTable(), 'role_id')) {
                $blueprint->integer('role_id')->nullable();

            }

            if (! Schema::hasColumn($blueprint->getTable(), 'custom_menu_id')) {
                $blueprint->integer('custom_menu_id')->nullable();

            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $blueprint): void {
            $blueprint->dropColumn('role_id');
            $blueprint->dropColumn('custom_menu_id');
        });
    }
};
