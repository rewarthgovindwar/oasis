<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfixVideoWatchesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('infix_video_watches', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('study_material_id')->nullable();
            $blueprint->integer('student_id')->nullable();
            $blueprint->string('time')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infix_video_watches');
    }
}
