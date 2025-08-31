<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmTeacherUploadContentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_teacher_upload_contents', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('content_title')->length(200)->nullable();
            $blueprint->string('content_type')->nullable()->comment('as assignment, st study material, sy sullabus, ot others download');
            $blueprint->integer('available_for_admin')->default(0)->nullable();
            $blueprint->integer('available_for_all_classes')->default(0);
            $blueprint->date('upload_date')->nullable();
            $blueprint->string('description')->length(500)->nullable();
            $blueprint->string('source_url')->nullable();
            $blueprint->string('upload_file')->length(200)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('course_id')->nullable();
            $blueprint->integer('parent_course_id')->nullable();

            $blueprint->integer('class')->nullable()->unsigned();
            $blueprint->foreign('class')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section')->nullable();
            // $table->foreign('section')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            // $table->created_at->format('Y-m-d');
            // $table->updated_at->format('Y-m-d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_teacher_upload_contents');
    }
}
