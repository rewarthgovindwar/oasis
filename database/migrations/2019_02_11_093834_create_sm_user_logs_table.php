<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_user_logs', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('ip_address')->nullable();
            $blueprint->string('user_agent')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('infix_roles')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_user_logs');
    }
}
