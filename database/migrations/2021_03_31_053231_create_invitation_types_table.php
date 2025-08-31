<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_invitation_types', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('invitation_id');
            $blueprint->enum('type', ['one-to-one', 'group', 'class-teacher'])->default('one-to-one');
            $blueprint->unsignedBigInteger('section_id')->nullable();
            $blueprint->unsignedBigInteger('class_teacher_id')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_types');
    }
}
