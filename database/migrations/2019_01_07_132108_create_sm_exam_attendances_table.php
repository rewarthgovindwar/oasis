<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_exam_attendances', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $blueprint->integer('exam_id')->nullable()->unsigned();
            $blueprint->foreign('exam_id')->references('id')->on('sm_exams')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

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
        Schema::dropIfExists('sm_exam_attendances');
    }
}
