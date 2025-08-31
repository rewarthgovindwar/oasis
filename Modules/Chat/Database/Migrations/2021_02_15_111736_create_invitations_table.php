<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_invitations', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('from')->unsigned();
            $blueprint->integer('to')->unsigned();
            $blueprint->tinyInteger('status')->default(0)
                ->comment('0- pending, 1- connected, 2- blocked');
            $blueprint->timestamps();

            //            $table->foreign('from')->references('id')->on('users');
            //            $table->foreign('to')->references('id')->on('users');
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
