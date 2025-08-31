<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLessonTopicDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_lesson_topic_details', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('lesson_id')->nullable();
            $blueprint->string('topic_title');
            $blueprint->string('completed_status')->nullable();
            $blueprint->date('competed_date')->nullable();

            $blueprint->integer('active_status')->default(1);

            $blueprint->integer('topic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('topic_id')->references('id')->on('sm_lesson_topics')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('user_id')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_lesson_topic_details');
    }
}
