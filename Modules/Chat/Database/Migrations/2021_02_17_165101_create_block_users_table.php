<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_block_users', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('block_by');
            $blueprint->unsignedBigInteger('block_to');
            $blueprint->timestamps();

            //            $table->foreign('block_by')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //
            //            $table->foreign('block_to')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_block_users');
    }
}
