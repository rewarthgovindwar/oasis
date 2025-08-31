<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineExamStudentAnswerMarkingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('online_exam_student_answer_markings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('online_exam_id')->nullable();
            $blueprint->integer('student_id')->nullable();
            $blueprint->integer('question_id')->nullable();
            $blueprint->string('user_answer')->nullable();
            $blueprint->string('answer_status')->nullable();
            $blueprint->integer('obtain_marks')->nullable();
            $blueprint->integer('school_id')->nullable();
            $blueprint->integer('marked_by')->default(0);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_student_answer_markings');
    }
}
