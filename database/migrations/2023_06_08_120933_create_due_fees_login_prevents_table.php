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

        Schema::create('due_fees_login_prevents', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('user_id')->nullable()->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('infix_roles')->onDelete('cascade');
            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->timestamps();
        });

        Schema::table('sm_general_settings', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_general_settings', 'due_fees_login')) {
                $blueprint->boolean('due_fees_login')->nullable()->default(0);
            }

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('due_fees_login_prevents');
    }
};
