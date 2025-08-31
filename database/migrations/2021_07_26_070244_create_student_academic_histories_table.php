<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentAcademicHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_academic_histories', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title');
            $blueprint->text('description')->nullable();
            $blueprint->boolean('active_status')->default(1);
            $blueprint->date('occurance_date');
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *`
     */
    public function down(): void
    {
        Schema::dropIfExists('student_academic_histories');
    }
}
