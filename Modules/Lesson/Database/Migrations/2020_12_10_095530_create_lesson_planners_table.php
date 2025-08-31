<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLessonPlannersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_planners', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('day')->nullable()->comment('1=sat,2=sun,7=fri');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('lesson_id')->nullable();
            $blueprint->integer('topic_id')->nullable();

            $blueprint->integer('lesson_detail_id');
            $blueprint->integer('topic_detail_id')->nullable();
            $blueprint->string('sub_topic')->nullable();
            $blueprint->text('lecture_youube_link')->nullable();
            $blueprint->text('lecture_vedio')->nullable();
            $blueprint->text('attachment')->nullable();
            $blueprint->text('teaching_method')->nullable();
            $blueprint->text('general_objectives')->nullable();

            $blueprint->text('previous_knowlege')->nullable();
            $blueprint->text('comp_question')->nullable();
            $blueprint->text('zoom_setup')->nullable();
            $blueprint->text('presentation')->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->date('lesson_date');
            $blueprint->date('competed_date')->nullable();
            $blueprint->string('completed_status')->nullable();

            $blueprint->integer('room_id')->nullable()->unsigned();
            $blueprint->foreign('room_id')->references('id')->on('sm_class_rooms')->onDelete('cascade');

            $blueprint->integer('teacher_id')->nullable()->unsigned();
            $blueprint->foreign('teacher_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('class_period_id')->nullable()->unsigned();
            $blueprint->foreign('class_period_id')->references('id')->on('sm_class_times')->onDelete('cascade');

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('routine_id')->nullable()->unsigned();

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
        Schema::dropIfExists('lesson_planners');
    }
}
