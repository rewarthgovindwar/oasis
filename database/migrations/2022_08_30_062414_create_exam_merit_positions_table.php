<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamMeritPositionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('exam_merit_positions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('class_id')->nullable();
            $blueprint->integer('section_id')->nullable();
            $blueprint->integer('exam_term_id')->nullable();
            $blueprint->integer('total_mark')->nullable();
            $blueprint->integer('position')->nullable();
            $blueprint->integer('admission_no')->nullable();
            $blueprint->float('gpa')->nullable();
            $blueprint->string('grade')->nullable();
            $blueprint->integer('record_id')->nullable();
            $blueprint->integer('school_id');
            $blueprint->integer('academic_id');
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_merit_positions');
    }
}
