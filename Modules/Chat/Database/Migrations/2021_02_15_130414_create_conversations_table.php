<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('from_id')->nullable();
            $blueprint->unsignedBigInteger('to_id')->nullable();
            $blueprint->text('message')->nullable();
            $blueprint->tinyInteger('status')->default(0)->comment('0 for unread,1 for seen');
            $blueprint->tinyInteger('message_type')->default(0)->comment('0- text message, 1- image, 2- pdf, 3- doc, 4- voice');
            $blueprint->text('file_name')->nullable();
            $blueprint->text('original_file_name')->nullable();
            $blueprint->boolean('initial')->default(0);
            $blueprint->unsignedBigInteger('reply')->nullable();
            $blueprint->unsignedBigInteger('forward')->nullable();
            $blueprint->boolean('deleted_by_to')->default(0);
            $blueprint->softDeletes();
            $blueprint->timestamps();
            //            $table->foreign('from_id')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //            $table->foreign('to_id')
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
        Schema::dropIfExists('chat_invitations');
    }
}
