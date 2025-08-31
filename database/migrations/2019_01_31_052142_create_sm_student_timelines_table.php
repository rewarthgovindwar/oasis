<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_student_timelines', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('staff_student_id');
            $blueprint->string('title')->nullable();
            $blueprint->date('date')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->string('file')->nullable();
            $blueprint->string('type')->nullable()->comment('stu=student,stf=staff');
            $blueprint->integer('visible_to_student')->default(0)->comment('0 = no, 1 = yes');
            $blueprint->integer('active_status')->default(1);
            $blueprint->timestamps();

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
        Schema::dropIfExists('sm_student_timelines');
    }
}
