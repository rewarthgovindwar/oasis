<?php

use App\CustomResultSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\RolePermission\Entities\InfixModuleInfo;

class CreateCustomResultSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_result_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('exam_type_id')->nullable();
            $blueprint->float('exam_percentage')->nullable();
            $blueprint->string('merit_list_setting');

            $blueprint->string('print_status')->nullable();
            $blueprint->string('profile_image')->nullable();
            $blueprint->string('header_background')->nullable();
            $blueprint->string('body_background')->nullable();

            $blueprint->integer('academic_year')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $store = new CustomResultSetting();
        $store->merit_list_setting = 'total_mark';
        $store->print_status = 'image';
        $store->save();
        $store = CustomResultSetting::first();
        if (! $store) {
            $store = new CustomResultSetting();
            $store->merit_list_setting = 'total_mark';
            $store->print_status = 'image';
        }

        $store->profile_image = 'image';
        $store->header_background = 'header';
        $store->body_background = 'body';
        $store->save();

        $permission = InfixModuleInfo::find(5000);
        if (! $permission) {
            $permission = new InfixModuleInfo();
            $permission->id = 5000;
            $permission->module_id = 9;
            $permission->parent_id = 870;
            $permission->type = '2';
            $permission->is_saas = 0;
            $permission->name = 'Position Setup';
            $permission->route = 'exam-report-position';
            $permission->lang_name = 'position_setup';
            $permission->active_status = 1;
            $permission->created_by = 1;
            $permission->updated_by = 1;
            $permission->school_id = 1;
            $permission->save();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_result_settings');
    }
}
