<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamSignaturesTable extends Migration
{
    public function up(): void
    {
        Schema::create('sm_exam_signatures', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->text('signature');
            $blueprint->tinyInteger('active_status')->default(1);

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $classRoutines = [
            'exam-signature-settings' => [
                'module' => null,
                'sidebar_menu' => null,
                'name' => 'Exam Signature Settings',
                'lang_name' => 'exam.exam_signature_settings',
                'icon' => null,
                'svg' => null,
                'route' => 'exam-signature-settings',
                'parent_route' => 'exam_settings',
                'is_admin' => 1,
                'is_teacher' => 0,
                'is_student' => 0,
                'is_parent' => 0,
                'position' => 2,
                'is_saas' => 0,
                'is_menu' => 1,
                'status' => 1,
                'menu_status' => 1,
                'relate_to_child' => 0,
                'alternate_module' => null,
                'permission_section' => 0,
                'user_id' => null,
                'type' => 2,
                'old_id' => null,
            ],
        ];
        foreach ($classRoutines as $classRoutine) {
            storePermissionData($classRoutine);
        }

        $exam_settings = App\SmExamSetting::withOutGlobalScopes()->whereNull('exam_type')->get();
        foreach ($exam_settings as $exam_setting) {
            $exam_signature = new App\Models\SmExamSignature();
            $exam_signature->title = $exam_setting->title;
            $exam_signature->signature = $exam_setting->file;
            $exam_signature->school_id = $exam_setting->school_id;
            $exam_signature->academic_id = $exam_setting->academic_id;
            $exam_signature->save();

            $exam_setting->title = null;
            $exam_setting->file = null;
            $exam_setting->save();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_exam_signatures');
    }
}
