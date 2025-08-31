<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmMarkStoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_mark_stores', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('student_roll_no')->default(1);
            $blueprint->integer('student_addmission_no')->default(1);
            $blueprint->float('total_marks')->default(0);
            $blueprint->tinyInteger('is_absent')->default(1);
            $blueprint->text('teacher_remarks')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $blueprint->integer('exam_term_id')->nullable()->unsigned();
            $blueprint->foreign('exam_term_id')->references('id')->on('sm_exam_types')->onDelete('cascade');

            $blueprint->integer('exam_setup_id')->nullable()->unsigned();
            $blueprint->foreign('exam_setup_id')->references('id')->on('sm_exam_setups')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->bigInteger('student_record_id')->nullable()->unsigned();
            $blueprint->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

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

        // $sql ="";
        // DB::insert($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_mark_stores');
    }
}
