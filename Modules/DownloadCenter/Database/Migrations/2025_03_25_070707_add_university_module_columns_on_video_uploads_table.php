<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniversityModuleColumnsOnVideoUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('video_uploads','un_session_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_session_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_faculty_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_faculty_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_department_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_department_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_academic_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_academic_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_semester_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_semester_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_semester_label_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_semester_label_id')->nullable();
            });
        }

        if(!Schema::hasColumn('video_uploads','un_section_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_section_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        if(Schema::hasColumn('video_uploads','un_session_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_session_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_faculty_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_faculty_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_department_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_department_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_academic_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_academic_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_semester_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_semester_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_semester_label_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_semester_label_id')->nullable();
            });
        }

        if(Schema::hasColumn('video_uploads','un_section_id'))
        {
            Schema::table('video_uploads', function (Blueprint $table) {
                $table->unsignedBigInteger('un_section_id')->nullable();
            });
        }
    }
}
