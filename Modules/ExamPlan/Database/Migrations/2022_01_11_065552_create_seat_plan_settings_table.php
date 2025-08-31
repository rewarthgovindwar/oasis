<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\ExamPlan\Entities\SeatPlanSetting;

class CreateSeatPlanSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seat_plan_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->boolean('school_name')->nullable();
            $blueprint->boolean('student_photo')->nullable();
            $blueprint->boolean('student_name')->nullable();
            $blueprint->boolean('admission_no')->nullable();
            $blueprint->boolean('class_section')->nullable();
            $blueprint->boolean('exam_name')->nullable();
            $blueprint->boolean('roll_no')->nullable();
            $blueprint->boolean('academic_year')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->timestamps();
        });

        $setting = SeatPlanSetting::first();
        if (! $setting) {
            $setting = new SeatPlanSetting();
            $setting->student_photo = 1;
            $setting->student_name = 1;
            $setting->admission_no = 1;
            $setting->class_section = 1;
            $setting->exam_name = 1;
            $setting->academic_year = 1;
            $setting->roll_no = 1;
            $setting->school_name = 1;
            $setting->save();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_plan_settings');
    }
}
