<?php

use App\SmGeneralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $generalSettings = SmGeneralSettings::first();
        if ($generalSettings) {
            $generalSettings->software_version = '9.0.1';
            $generalSettings->update();
        }

        if (Schema::hasTable('speech_sliders') && ! Schema::hasColumn('speech_sliders', 'title')) {
            Schema::table('speech_sliders', function (Blueprint $blueprint): void {
                $blueprint->string('title')->nullable()->after('designation')->nullable();
            });
        }

        Permission::where('route', 'accounts-report')->where('sidebar_menu', 'accounts_report')->update([
            'parent_route' => null,
        ]);

        if (! Schema::hasColumn('sm_news', 'mark_as_archive')) {
            Schema::table('sm_news', function (Blueprint $blueprint): void {
                $blueprint->tinyInteger('mark_as_archive')->default(0)->after('publish_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
