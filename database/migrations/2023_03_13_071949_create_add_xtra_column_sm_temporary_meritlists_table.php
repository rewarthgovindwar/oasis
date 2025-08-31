<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddXtraColumnSmTemporaryMeritlistsTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sm_temporary_meritlists')) {
            Schema::table('sm_temporary_meritlists', function (Blueprint $blueprint): void {
                if (! Schema::hasColumn('sm_temporary_meritlists', 'roll_no')) {
                    $blueprint->integer('roll_no')->nullable();
                }
            });
        }

        Schema::table('custom_result_settings', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('custom_result_settings', 'vertical_boarder')) {
                $blueprint->string('vertical_boarder')->nullable();
            }
        });

    }

    public function down(): void
    {

        Schema::table('sm_temporary_meritlists', function (Blueprint $blueprint): void {
            if (Schema::hasColumn('sm_temporary_meritlists', 'roll_no')) {
                $blueprint->dropColumn('roll_no');
            }
        });
        Schema::table('custom_result_settings', function (Blueprint $blueprint): void {
            if (Schema::hasColumn('custom_result_settings', 'vertical_boarder')) {
                $blueprint->dropColumn('vertical_boarder');
            }
        });
    }
}
