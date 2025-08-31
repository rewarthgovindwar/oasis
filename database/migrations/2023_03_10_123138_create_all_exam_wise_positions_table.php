<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllExamWisePositionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('all_exam_wise_positions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('class_id')->nullable();
            $blueprint->integer('section_id')->nullable();
            $blueprint->float('total_mark')->nullable();
            $blueprint->integer('position')->nullable();
            $blueprint->integer('roll_no')->nullable();
            $blueprint->integer('admission_no')->nullable();
            $blueprint->float('gpa')->nullable();
            $blueprint->float('grade')->nullable();
            $blueprint->integer('record_id')->nullable();
            $blueprint->integer('school_id');
            $blueprint->integer('academic_id');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('all_exam_wise_positions');
    }
}
