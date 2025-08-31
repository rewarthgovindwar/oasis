<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentRecordTemporariesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_record_temporaries', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('sm_student_id')->unsigned();
            $blueprint->foreign('sm_student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->bigInteger('student_record_id')->unsigned();
            $blueprint->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

            $blueprint->integer('user_id')->nullable();

            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_record_temporaries');
    }
}
