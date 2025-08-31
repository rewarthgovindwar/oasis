<?php

use App\InfixModuleManager;
use App\SmGeneralSettings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exist = InfixModuleManager::where('name', 'ToyyibPay')->first();
        if (! $exist) {
            $name = 'ToyyibPay';
            $infixModuleManager = new InfixModuleManager();
            $infixModuleManager->name = $name;
            $infixModuleManager->email = 'support@spondonit.com';
            $infixModuleManager->notes = 'This is ToyyibPay module for Online payemnt. Thanks for using.';
            $infixModuleManager->version = '1.0';
            $infixModuleManager->update_url = 'https://spondonit.com/contact';
            $infixModuleManager->is_default = 0;
            $infixModuleManager->addon_url = 'https://codecanyon.net/item/infixedu-zoom-live-class/27623128?s_rank=12';
            $infixModuleManager->installed_domain = url('/');
            $infixModuleManager->activated_date = date('Y-m-d');
            $infixModuleManager->save();
        }

        $generalSettings = SmGeneralSettings::first();
        if ($generalSettings) {
            $generalSettings->software_version = '8.2.0';
            $generalSettings->update();
        }

        $permissions = [
            'fees_collect_student_wise' => [
                'module' => null,
                'sidebar_menu' => 'system_settings',
                'name' => 'Fees Collect Student Wise',
                'lang_name' => 'Fees Collect Student Wise',
                'icon' => null,
                'svg' => null,
                'route' => 'fees_collect_student_wise',
                'parent_route' => 'collect_fees',
                'is_admin' => 1,
                'is_teacher' => 0,
                'is_student' => 0,
                'is_parent' => 0,
                'position' => 3,
                'is_saas' => 0,
                'is_menu' => 0,
                'status' => 1,
                'menu_status' => 0,
                'relate_to_child' => 0,
                'alternate_module' => null,
                'permission_section' => 0,
                'section_id' => 1,
                'user_id' => null,
                'type' => 3,
                'old_id' => null,
                'child' => [],
            ],
        ];
        foreach ($permissions as $permission) {
            storePermissionData($permission);
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
