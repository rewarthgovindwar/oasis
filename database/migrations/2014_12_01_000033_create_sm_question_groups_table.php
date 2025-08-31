<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmQuestionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_question_groups', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title', 200);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();
            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_question_groups');
    }
}
