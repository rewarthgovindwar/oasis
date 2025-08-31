<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmOnlineExamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_online_exams', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title')->nullable();
            $blueprint->date('date')->nullable()->nullable();
            $blueprint->string('start_time', 200)->nullable();
            $blueprint->string('end_time', 200)->nullable();
            $blueprint->string('end_date_time')->nullable();
            $blueprint->integer('percentage')->nullable();
            $blueprint->text('instruction')->nullable();
            $blueprint->tinyInteger('status')->nullable()->comment('0 = Pending 1 Published');
            $blueprint->tinyInteger('is_taken')->default(0)->nullable();
            $blueprint->tinyInteger('is_closed')->default(0)->nullable();
            $blueprint->tinyInteger('is_waiting')->default(0)->nullable();
            $blueprint->tinyInteger('is_running')->default(0)->nullable();
            $blueprint->tinyInteger('auto_mark')->default(0)->nullable();
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
        Schema::dropIfExists('sm_online_exams');
    }
}
