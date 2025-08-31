<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_homeworks', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('homework_date')->nullable();
            $blueprint->date('submission_date')->nullable();
            $blueprint->date('evaluation_date')->nullable();
            $blueprint->string('file', 200)->nullable();
            $blueprint->string('marks', 200)->nullable();
            $blueprint->string('description', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('evaluated_by')->nullable()->unsigned();
            $blueprint->foreign('evaluated_by')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('section_id')->nullable();
            // $table->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

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
        Schema::dropIfExists('sm_homeworks');
    }
}
