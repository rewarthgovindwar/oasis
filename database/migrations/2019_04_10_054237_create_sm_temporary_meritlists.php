<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmTemporaryMeritlists extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_temporary_meritlists', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('iid', 250)->nullable();
            $blueprint->string('student_id', 250)->nullable();
            $blueprint->float('merit_order')->nullable();
            $blueprint->string('student_name', 250)->nullable();
            $blueprint->string('admission_no', 250)->nullable();
            $blueprint->string('subjects_id_string', 250)->nullable();
            $blueprint->string('subjects_string', 250)->nullable();
            $blueprint->string('marks_string', 250)->nullable();
            $blueprint->float('total_marks')->nullable();
            $blueprint->float('average_mark', 20)->nullable();
            $blueprint->float('gpa_point', 20)->nullable();
            $blueprint->string('result', 250)->nullable();
            $blueprint->timestamps();

            $blueprint->integer('exam_id')->nullable()->unsigned();
            $blueprint->foreign('exam_id')->references('id')->on('sm_exams')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

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
        Schema::dropIfExists('sm_temporary_meritlists');
    }
}
