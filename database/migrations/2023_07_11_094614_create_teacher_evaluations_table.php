<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_evaluations', function (Blueprint $blueprint): void {
            $blueprint->id();

            $blueprint->text('rating')->nullable();

            $blueprint->string('comment')->nullable();

            $blueprint->boolean('status')->nullable()->default(false);

            $blueprint->integer('record_id')->unsigned();

            $blueprint->integer('subject_id')->nullable()->unsigned();

            $blueprint->integer('teacher_id')->nullable()->unsigned();

            $blueprint->integer('student_id')->nullable()->unsigned();

            $blueprint->integer('role_id')->nullable()->unsigned();

            $blueprint->integer('parent_id')->nullable()->unsigned();

            $blueprint->integer('academic_id')->nullable()->unsigned();

            $blueprint->integer('school_id')->default(1)->unsigned();

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluations');
    }
}
