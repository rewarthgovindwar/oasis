<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_notifications', function (Blueprint $blueprint): void {

            $blueprint->increments('id');
            $blueprint->date('date')->nullable();
            $blueprint->string('message')->nullable();
            $blueprint->string('url')->nullable();
            $blueprint->tinyInteger('is_read')->default(0);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('user_id')->default(1)->nullable()->unsigned();

            $blueprint->integer('role_id')->default(1)->unsigned();

            $blueprint->integer('created_by')->default(1)->unsigned();

            $blueprint->integer('updated_by')->default(1)->unsigned();

            $blueprint->integer('school_id')->default(1)->unsigned();
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
        Schema::dropIfExists('sm_notifications');
    }
}
