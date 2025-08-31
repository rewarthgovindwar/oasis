<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\RolePermission\Entities\InfixModuleInfo;

class AddLessonPlanSubtopicToGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sm_general_settings', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn($blueprint->getTable(), 'sub_topic_enable')) {
                $blueprint->boolean('sub_topic_enable')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sm_general_settings', function (Blueprint $blueprint): void {
            if (Schema::hasColumn($blueprint->getTable(), 'sub_topic_enable')) {
                $blueprint->dropColumn('sub_topic_enable');
            }
        });

        InfixModuleInfo::where('id', 835)->delete();
    }
}
