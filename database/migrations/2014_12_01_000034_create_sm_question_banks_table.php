<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmQuestionBanksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_question_banks', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('type', 2)->comment('M for multi ans, T for trueFalse, F for fill in the blanks');
            $blueprint->text('question')->nullable();
            $blueprint->integer('marks')->nullable();
            $blueprint->string('trueFalse', 1)->nullable()->comment('F = false, T = true ');
            $blueprint->text('suitable_words')->nullable();
            $blueprint->string('number_of_option', 2)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('q_group_id')->nullable()->unsigned();
            $blueprint->foreign('q_group_id')->references('id')->on('sm_question_groups')->onDelete('cascade');

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
        Schema::dropIfExists('sm_question_banks');
    }
}
