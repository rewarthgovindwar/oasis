<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmOnlineExamQuestionMuOptionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_online_exam_question_mu_options', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title')->nullable();
            $blueprint->tinyInteger('status')->nullable()->comment('0 unchecked 1 checked');
            $blueprint->tinyInteger('active_status')->default(1);

            $blueprint->integer('online_exam_question_id')->nullable()->unsigned()->comment('here we use foreign key shorter name');
            $blueprint->foreign('online_exam_question_id', 'on_ex_qu_id')->references('id')->on('sm_online_exam_questions')->onDelete('cascade');

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
        Schema::dropIfExists('sm_online_exam_question_mu_options');
    }
}
