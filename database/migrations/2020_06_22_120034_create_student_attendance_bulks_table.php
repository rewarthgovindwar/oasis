<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentAttendanceBulksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_attendance_bulks', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('attendance_date')->nullable();
            // $table->string('in_time')->nullable();
            // $table->string('out_time')->nullable();
            $blueprint->string('attendance_type')->nullable();
            $blueprint->string('note')->nullable();
            $blueprint->integer('student_id')->nullable();
            $blueprint->integer('student_record_id')->nullable();
            $blueprint->integer('class_id')->nullable();
            $blueprint->integer('section_id')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance_bulks');
    }
}
