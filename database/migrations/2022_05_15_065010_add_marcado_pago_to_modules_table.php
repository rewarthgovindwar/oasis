<?php

use App\InfixModuleManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarcadoPagoToModulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $infixModuleManager = new InfixModuleManager();
        $infixModuleManager->name = 'MercadoPago';
        $infixModuleManager->email = 'support@spondonit.com';
        $infixModuleManager->notes = 'This is MercadoPago Payment Module For Online Payment. Thanks For Using.';
        $infixModuleManager->version = '1.0';
        $infixModuleManager->update_url = 'https://spondonit.com/contact';
        $infixModuleManager->is_default = 0;
        $infixModuleManager->addon_url = 'https://spondonit.com/contact';
        $infixModuleManager->installed_domain = url('/');
        $infixModuleManager->activated_date = date('Y-m-d');
        $infixModuleManager->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $blueprint): void {
            //
        });
    }
}
