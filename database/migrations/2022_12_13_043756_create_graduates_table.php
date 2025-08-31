<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduatesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('graduates', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');
            $blueprint->integer('created_by')->nullable()->unsigned();
            $blueprint->integer('un_department_id')->nullable();
            $blueprint->integer('un_faculty_id')->nullable();
            $blueprint->integer('graduation_date')->nullable();
            $blueprint->integer('un_session_id')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('session_id')->nullable()->unsigned();
            $blueprint->foreign('session_id')->references('id')->on('sm_sessions')->onDelete('cascade');
            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');
            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
}
