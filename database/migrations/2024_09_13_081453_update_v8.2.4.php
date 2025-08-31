<?php

use App\SmGeneralSettings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $generalSettings = SmGeneralSettings::first();
        if ($generalSettings) {
            $generalSettings->software_version = '8.2.4';
            $generalSettings->update();
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
