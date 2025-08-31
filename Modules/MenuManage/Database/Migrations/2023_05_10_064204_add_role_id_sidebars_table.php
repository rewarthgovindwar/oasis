<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdSidebarsTable extends Migration
{
    public function up(): void
    {
        Schema::table('sidebars', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sidebars', 'role_id')) {
                $blueprint->integer('role_id')->nullable();
            }

            if (! Schema::hasColumn('sidebars', 'ignore')) {
                $blueprint->integer('ignore')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('', function (Blueprint $blueprint): void {});
    }
}
