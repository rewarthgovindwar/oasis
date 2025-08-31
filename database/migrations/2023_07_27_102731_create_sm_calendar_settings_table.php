<?php

use App\Models\SmCalendarSetting;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_calendar_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('menu_name');
            $blueprint->tinyInteger('status')->default(0);
            $blueprint->string('font_color');
            $blueprint->string('bg_color');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $menuNames = [
            '#008000' => 'admission_query',
            '#000000' => 'lesson_plan',
            '#FF0000' => 'homework',
            '#800080' => 'study_material',
            '#000080' => 'exam',
            '#808000' => 'online_exam',
            '#008080' => 'leave',
            '#00FFFF' => 'notice_board',
            '#808080' => 'holiday',
            '#800000' => 'event',
            '#800009' => 'library',
        ];

        $schools = SmSchool::get();
        foreach ($schools as $school) {
            foreach ($menuNames as $key => $menuName) {
                $storeData = new SmCalendarSetting();
                $storeData->menu_name = $menuName;
                $storeData->status = 1;
                $storeData->font_color = '#FFFFFF';
                $storeData->bg_color = $key;
                $storeData->school_id = $school->id;
                $storeData->save();
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_calendar_settings');
    }
};
