<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmClassRoutineUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_class_routine_updates', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('day')->nullable()->comment('1=sat,2=sun,7=fri');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->time('start_time')->nullable();
            $blueprint->time('end_time')->nullable();
            $blueprint->tinyInteger('is_break')->nullable()->comment('1 = tiffin time, 0 = class');

            $blueprint->integer('room_id')->nullable()->unsigned();
            $blueprint->foreign('room_id')->references('id')->on('sm_class_rooms')->onDelete('cascade');

            $blueprint->integer('teacher_id')->nullable()->unsigned();
            $blueprint->foreign('teacher_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('class_period_id')->nullable()->unsigned();
            $blueprint->foreign('class_period_id')->references('id')->on('sm_class_times')->onDelete('cascade');

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_class_routine_updates');
    }
}
