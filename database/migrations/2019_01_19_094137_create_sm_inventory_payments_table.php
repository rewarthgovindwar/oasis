<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmInventoryPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_inventory_payments', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('item_receive_sell_id')->nullable()->unsigned();
            $blueprint->date('payment_date')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('reference_no', 50)->nullable();
            $blueprint->string('payment_type')->length(11)->nullable()->comment('R for receive S for sell');
            $blueprint->integer('payment_method')->nullable()->unsigned();
            $blueprint->string('notes')->length(500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_inventory_payments');
    }
}
