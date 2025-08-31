<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_statuses', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->tinyInteger('status')->default(0)->comment('0- inactive, 1- active, 2- away, 3- busy');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_statuses');
    }
}
