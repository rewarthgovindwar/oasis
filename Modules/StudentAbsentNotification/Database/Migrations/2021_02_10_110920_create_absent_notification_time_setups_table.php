<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsentNotificationTimeSetupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absent_notification_time_setups', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('time_from')->nullable();
            $blueprint->string('time_to')->nullable();
            $blueprint->integer('active_status')->default(1);
            $blueprint->integer('school_id')->default(1);

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absent_notification_time_setups');
    }
}
