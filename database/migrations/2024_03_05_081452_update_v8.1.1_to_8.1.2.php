<?php

use App\InfixModuleManager;
use App\SmGeneralSettings;
use App\SmHeaderMenuManager;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exist = InfixModuleManager::where('name', 'Certificate')->first();
        if (! $exist) {
            $infixModuleManager = new InfixModuleManager();
            $infixModuleManager->name = 'Certificate';
            $infixModuleManager->email = 'support@spondonit.com';
            $infixModuleManager->notes = "This is the module to generate Certificate's for students and employees.";
            $infixModuleManager->version = '1.0';
            $infixModuleManager->update_url = 'https://spondonit.com/contact';
            $infixModuleManager->is_default = 0;
            $infixModuleManager->addon_url = 'maito:support@spondonit.com';
            $infixModuleManager->installed_domain = url('/');
            $infixModuleManager->activated_date = date('Y-m-d');
            $infixModuleManager->save();
        }

        $extraContactPage = SmHeaderMenuManager::where([
            ['type', 'sPages'],
            ['title', 'Contact'],
            ['link', '/contact-us'],
            ['parent_id', null],
        ])->latest()->first();
        if ($extraContactPage) {
            $extraContactPage->delete();
        }

        $generalSettings = SmGeneralSettings::first();
        if ($generalSettings) {
            $generalSettings->software_version = '8.1.2';
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
