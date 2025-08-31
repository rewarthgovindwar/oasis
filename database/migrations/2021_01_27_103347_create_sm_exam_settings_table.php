<?php

use App\SmExamSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_exam_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('exam_type')->nullable();
            $blueprint->string('title')->nullable();
            $blueprint->date('publish_date')->nullable();
            $blueprint->date('start_date')->nullable();
            $blueprint->date('end_date')->nullable();
            $blueprint->string('file', 200)->nullable();
            $blueprint->tinyInteger('active_status')->nullable()->default(1);
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        $smExamSetting = new SmExamSetting();
        $smExamSetting->exam_type = 1;
        $smExamSetting->title = 'Exam Controller';
        $smExamSetting->publish_date = date('Y-m-d h:i:s');
        $smExamSetting->start_date = date('Y-m-d h:i:s');
        $smExamSetting->end_date = date('Y-m-d h:i:s');
        $smExamSetting->file = 'public/uploads/exam/signature.png';
        $smExamSetting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_exam_settings');
    }
}
