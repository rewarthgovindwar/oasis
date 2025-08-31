<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamScheduleSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_exam_schedule_subjects', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('date')->nullable();
            $blueprint->string('start_time', 200)->nullable();
            $blueprint->string('end_time', 200)->nullable();
            $blueprint->string('room', 200)->nullable();
            $blueprint->integer('full_mark')->nullable();
            $blueprint->integer('pass_mark')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('exam_schedule_id')->nullable()->unsigned();
            $blueprint->foreign('exam_schedule_id')->references('id')->on('sm_exam_schedules')->onDelete('cascade');

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

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
        Schema::dropIfExists('sm_exam_schedule_subjects');
    }
}
