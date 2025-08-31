<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentIdCardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_student_id_cards', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title')->nullable();
            $blueprint->string('logo')->nullable();
            $blueprint->string('signature')->nullable();
            $blueprint->string('background_img')->nullable();
            $blueprint->string('profile_image')->nullable();
            $blueprint->text('role_id')->nullable();
            $blueprint->string('page_layout_style')->nullable();
            $blueprint->string('user_photo_style')->nullable();
            $blueprint->string('user_photo_width')->nullable();
            $blueprint->string('user_photo_height')->nullable();
            $blueprint->integer('pl_width')->nullable();
            $blueprint->integer('pl_height')->nullable();
            $blueprint->integer('t_space')->nullable();
            $blueprint->integer('b_space')->nullable();
            $blueprint->integer('r_space')->nullable();
            $blueprint->integer('l_space')->nullable();
            $blueprint->string('admission_no')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('student_name')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('class')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('father_name')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('mother_name')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('student_address')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('phone_number')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('dob')->default(0)->comment('0 for no 1 for yes');
            $blueprint->string('blood')->default(0)->comment('0 for no 1 for yes');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

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
        Schema::dropIfExists('sm_student_id_cards');
    }
}
