<?php

use App\InfixModuleManager;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exist = InfixModuleManager::where('name', 'QRCodeAttendance')->first();
        if (! $exist) {
            $name = 'QRCodeAttendance';
            $infixModuleManager = new InfixModuleManager();
            $infixModuleManager->name = $name;
            $infixModuleManager->email = 'support@spondonit.com';
            $infixModuleManager->notes = 'Welcome to the QRCodeAttendance, Module: Thanks for using';
            $infixModuleManager->version = '1.0';
            $infixModuleManager->update_url = 'https://spondonit.com/contact';
            $infixModuleManager->is_default = 0;
            $infixModuleManager->addon_url = 'https://codecanyon.net/item/infixedu-zoom-live-class/27623128?s_rank=12';
            $infixModuleManager->installed_domain = url('/');
            $infixModuleManager->activated_date = date('Y-m-d');
            $infixModuleManager->save();
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
