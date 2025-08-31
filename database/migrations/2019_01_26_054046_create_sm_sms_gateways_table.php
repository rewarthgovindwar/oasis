<?php

use App\SmSmsGateway;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmSmsGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_sms_gateways', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('gateway_name', 255)->nullable();
            $blueprint->string('type', 5)->nullable()->default('com');
            // $table->integer('gateway_id')->nullable();
            $blueprint->string('clickatell_username', 255)->nullable();
            $blueprint->string('clickatell_password', 255)->nullable();
            $blueprint->string('clickatell_api_id', 255)->nullable();
            $blueprint->string('twilio_account_sid', 255)->nullable();
            $blueprint->string('twilio_authentication_token', 255)->nullable();
            $blueprint->string('twilio_registered_no', 255)->nullable();
            $blueprint->string('msg91_authentication_key_sid', 255)->nullable();
            $blueprint->string('msg91_sender_id', 255)->nullable();
            $blueprint->string('msg91_route', 255)->nullable();
            $blueprint->string('msg91_country_code', 255)->nullable();

            $blueprint->string('textlocal_username', 255)->nullable();
            $blueprint->string('textlocal_hash', 255)->nullable();
            $blueprint->string('textlocal_sender', 255)->nullable();
            $blueprint->text('device_info')->nullable();

            $blueprint->string('africatalking_username', 255)->nullable();
            $blueprint->string('africatalking_api_key', 255)->nullable();

            $blueprint->tinyInteger('active_status')->default(0);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        $gateway = new SmSmsGateway();
        $gateway->gateway_name = 'Twilio';
        $gateway->save();

        $gateway = new SmSmsGateway();
        $gateway->gateway_name = 'Msg91';
        $gateway->save();

        $gateway = new SmSmsGateway();
        $gateway->gateway_name = 'TextLocal';
        $gateway->textlocal_sender = 'TXTLCL';
        $gateway->save();

        $gateway = new SmSmsGateway();
        $gateway->gateway_name = 'AfricaTalking';
        $gateway->africatalking_username = 'sandbox';
        $gateway->save();

        $gateway = new SmSmsGateway();
        $gateway->gateway_name = 'Mobile SMS';
        $gateway->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_sms_gateways');
    }
}
