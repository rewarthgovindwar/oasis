<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('sm_student_attendances', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('attendance_type', 10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $blueprint->string('notes', 500)->nullable();
            $blueprint->date('attendance_date')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->bigInteger('student_record_id')->nullable()->unsigned();
            // $table->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

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

            $blueprint->integer('active_status')->nullable()->default(1);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_student_attendances');
    }
}
