<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_group_users', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->uuid('group_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->integer('role')->default(1);
            $blueprint->unsignedBigInteger('added_by');
            $blueprint->unsignedBigInteger('removed_by')->nullable();
            $blueprint->dateTime('deleted_at')->nullable();
            $blueprint->timestamps();

            //            $table->foreign('group_id')
            //                ->references('id')->on('chat_groups')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //
            //            $table->foreign('user_id')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //
            //            $table->foreign('removed_by')
            //                ->references('id')->on('users')
            //                ->onUpdate('cascade')
            //                ->onDelete('cascade');
            //
            //            $table->foreign('added_by')
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
        Schema::dropIfExists('chat_group_users');
    }
}
