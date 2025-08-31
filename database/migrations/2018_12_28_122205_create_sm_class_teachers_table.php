<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmClassTeachersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_class_teachers', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('teacher_id')->nullable()->unsigned();
            $blueprint->foreign('teacher_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('assign_class_teacher_id')->nullable()->unsigned();
            $blueprint->foreign('assign_class_teacher_id')->references('id')->on('sm_assign_class_teachers')->onDelete('cascade');

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
        Schema::dropIfExists('sm_class_teachers');
    }
}
