<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMessageRemovesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_group_message_removes', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('group_message_recipient_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->timestamps();

            //            $table->foreign('group_message_recipient_id')
            //                ->references('id')->on('chat_group_message_recipients')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //            $table->foreign('user_id')
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
        Schema::dropIfExists('chat_group_message_removes');
    }
}
