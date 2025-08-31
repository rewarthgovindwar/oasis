<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmClassRoutinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_class_routines', function (Blueprint $blueprint): void {
            $blueprint->increments('id');

            $blueprint->string('monday', 200)->nullable();
            $blueprint->string('monday_start_from', 200)->nullable();
            $blueprint->string('monday_end_to', 200)->nullable();
            $blueprint->integer('monday_room_id')->unsigned()->nullable();

            $blueprint->string('tuesday', 200)->nullable();
            $blueprint->string('tuesday_start_from', 200)->nullable();
            $blueprint->string('tuesday_end_to', 200)->nullable();
            $blueprint->integer('tuesday_room_id')->unsigned()->nullable();

            $blueprint->string('wednesday', 200)->nullable();
            $blueprint->string('wednesday_start_from', 200)->nullable();
            $blueprint->string('wednesday_end_to', 200)->nullable();
            $blueprint->integer('wednesday_room_id')->unsigned()->nullable();

            $blueprint->string('thursday', 200)->nullable();
            $blueprint->string('thursday_start_from', 200)->nullable();
            $blueprint->string('thursday_end_to', 200)->nullable();
            $blueprint->integer('thursday_room_id')->unsigned()->nullable();

            $blueprint->string('friday', 200)->nullable();
            $blueprint->string('friday_start_from', 200)->nullable();
            $blueprint->string('friday_end_to', 200)->nullable();
            $blueprint->integer('friday_room_id')->unsigned()->nullable();

            $blueprint->string('saturday', 200)->nullable();
            $blueprint->string('saturday_start_from', 200)->nullable();
            $blueprint->string('saturday_end_to', 200)->nullable();
            $blueprint->integer('saturday_room_id')->unsigned()->nullable();

            $blueprint->string('sunday', 200)->nullable();
            $blueprint->string('sunday_start_from', 200)->nullable();
            $blueprint->string('sunday_end_to', 200)->nullable();
            $blueprint->integer('sunday_room_id')->unsigned()->nullable();

            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('subject_id')->nullable()->unsigned();
            $blueprint->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

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
        Schema::dropIfExists('sm_class_routines');
    }
}
