<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentTakeOnlineExamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_student_take_online_exams', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('status')->default(0)->comment('0=Not Yet, 1 = alreday submitted, 2 = got marks');
            $blueprint->tinyInteger('student_done')->default(0)->comment('0=Not Yet, 1 = complete');
            $blueprint->integer('total_marks')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('online_exam_id')->nullable()->unsigned();
            $blueprint->foreign('online_exam_id')->references('id')->on('sm_online_exams')->onDelete('cascade');

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
        Schema::dropIfExists('sm_student_take_online_exams');
    }
}
