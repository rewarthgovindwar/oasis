<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmPaymentMethhodsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_payment_methhods', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('method', 255);
            $blueprint->string('type')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('gateway_id')->nullable()->unsigned();
            $blueprint->foreign('gateway_id')->references('id')->on('sm_payment_gateway_settings')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

        });

        DB::table('sm_payment_methhods')->insert([
            [
                'method' => 'Cash',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'method' => 'Cheque',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'method' => 'Bank',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'method' => 'PayPal',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],

            [
                'method' => 'Stripe',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'method' => 'Paystack',
                'type' => 'System',
                'created_at' => date('Y-m-d h:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_payment_methhods');
    }
}
