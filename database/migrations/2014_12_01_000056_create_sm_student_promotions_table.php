<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_student_promotions', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('result_status', 10)->nullable();
            $blueprint->timestamps();

            $blueprint->integer('previous_class_id')->nullable()->unsigned();
            $blueprint->foreign('previous_class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('current_class_id')->nullable()->unsigned();
            $blueprint->foreign('current_class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('previous_section_id')->nullable()->unsigned();
            $blueprint->foreign('previous_section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('current_section_id')->nullable()->unsigned();
            $blueprint->foreign('current_section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('previous_session_id')->nullable()->unsigned();
            $blueprint->foreign('previous_session_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('current_session_id')->nullable()->unsigned();
            $blueprint->foreign('current_session_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('admission_number')->nullable();
            $blueprint->longText('student_info')->nullable();
            $blueprint->longText('merit_student_info')->nullable();

            $blueprint->integer('previous_roll_number')->nullable();
            $blueprint->integer('current_roll_number')->nullable();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        //  Schema::table('sm_student_promotions', function($table) {
        //     $table->foreign('student_id')->references('id')->on('sm_students');

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_student_promotions');
    }
}
