<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmMarksSendSmsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_marks_send_sms', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('sms_send_status')->default(1);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('exam_id')->nullable()->unsigned();
            $blueprint->foreign('exam_id')->references('id')->on('sm_exams')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        //  Schema::table('sm_marks_send_sms', function($table) {
        //     $table->foreign('exam_id')->references('id')->on('sm_exams');
        //     $table->foreign('student_id')->references('id')->on('sm_students');

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_marks_send_sms');
    }
}
