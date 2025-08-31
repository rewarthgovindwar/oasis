<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlanTopicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_plan_topics', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('sub_topic_title');

            $blueprint->integer('topic_id')->nullable()->unsigned();
            $blueprint->foreign('topic_id')->references('id')->on('sm_lesson_topic_details')->onDelete('cascade');

            $blueprint->integer('lesson_planner_id')->nullable()->unsigned();
            $blueprint->foreign('lesson_planner_id')->references('id')->on('lesson_planners')->onDelete('cascade');

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plan_topics');
    }
}
