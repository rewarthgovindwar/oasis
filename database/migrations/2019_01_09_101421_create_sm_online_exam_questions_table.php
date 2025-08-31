<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmOnlineExamQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_online_exam_questions', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('type', 1)->nullable();
            $blueprint->integer('mark')->nullable();
            $blueprint->text('title')->nullable();
            $blueprint->string('trueFalse', 1)->nullable()->comment('F = false, T = true ');
            $blueprint->text('suitable_words')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('online_exam_id')->nullable()->unsigned();
            $blueprint->foreign('online_exam_id')->references('id')->on('sm_online_exams')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_online_exam_questions');
    }
}
