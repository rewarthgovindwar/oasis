<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudyMaterialHomework extends Migration
{
    public function up(): void
    {
        Schema::table('sm_homeworks', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_homeworks', 'course_id')) {
                $blueprint->unsignedBigInteger('course_id')->nullable();
            }

            if (! Schema::hasColumn('sm_homeworks', 'lesson_id')) {
                $blueprint->unsignedBigInteger('lesson_id')->nullable();
            }

            if (! Schema::hasColumn('sm_homeworks', 'chapter_id')) {
                $blueprint->unsignedBigInteger('chapter_id')->nullable();
            }
        });

        Schema::table('sm_teacher_upload_contents', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_teacher_upload_contents', 'course_id')) {
                $blueprint->unsignedBigInteger('course_id')->nullable();
            }

            if (! Schema::hasColumn('sm_teacher_upload_contents', 'chapter_id')) {
                $blueprint->unsignedBigInteger('chapter_id')->nullable();
            }

            if (! Schema::hasColumn('sm_teacher_upload_contents', 'lesson_id')) {
                $blueprint->unsignedBigInteger('lesson_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
