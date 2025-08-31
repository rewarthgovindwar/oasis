<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomSmsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_sms_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('gateway_id');
            $blueprint->string('gateway_name');
            $blueprint->string('set_auth')->nullable();
            $blueprint->string('gateway_url');
            $blueprint->string('request_method');
            $blueprint->string('send_to_parameter_name');
            $blueprint->string('messege_to_parameter_name');

            $blueprint->string('param_key_1')->nullable();
            $blueprint->string('param_value_1')->nullable();

            $blueprint->string('param_key_2')->nullable();
            $blueprint->string('param_value_2')->nullable();

            $blueprint->string('param_key_3')->nullable();
            $blueprint->string('param_value_3')->nullable();

            $blueprint->string('param_key_4')->nullable();
            $blueprint->string('param_value_4')->nullable();

            $blueprint->string('param_key_5')->nullable();
            $blueprint->string('param_value_5')->nullable();

            $blueprint->string('param_key_6')->nullable();
            $blueprint->string('param_value_6')->nullable();

            $blueprint->string('param_key_7')->nullable();
            $blueprint->string('param_value_7')->nullable();

            $blueprint->string('param_key_8')->nullable();
            $blueprint->string('param_value_8')->nullable();
            $blueprint->integer('school_id')->default(1);
            $blueprint->timestamps();
        });

        $gateway_type = 'gateway_type';

        if (! Schema::hasColumn('sm_sms_gateways', $gateway_type)) {
            Schema::table('sm_sms_gateways', function ($table): void {
                $table->string('gateway_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_sms_settings');
    }
}
