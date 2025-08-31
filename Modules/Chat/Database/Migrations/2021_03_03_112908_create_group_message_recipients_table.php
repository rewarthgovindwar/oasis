<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMessageRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_group_message_recipients', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->unsignedBigInteger('conversation_id');
            $blueprint->string('group_id');
            $blueprint->dateTime('read_at')->nullable();
            $blueprint->softDeletes();
            $blueprint->timestamps();

            //            $table->foreign('user_id')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //
            //            $table->foreign('conversation_id')
            //                ->references('id')->on('chat_conversations')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_group_message_recipients');
    }
}
