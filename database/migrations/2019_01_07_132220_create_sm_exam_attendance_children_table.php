<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamAttendanceChildrenTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_exam_attendance_children', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('attendance_type', 2)->nullable()->comment('P = present A = Absent');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('exam_attendance_id')->nullable()->unsigned();
            $blueprint->foreign('exam_attendance_id')->references('id')->on('sm_exam_attendances')->onDelete('cascade');

            $blueprint->bigInteger('student_record_id')->nullable()->unsigned();
            $blueprint->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

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
        Schema::dropIfExists('sm_exam_attendance_children');
    }
}
