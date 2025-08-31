<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmAmountTransfersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_amount_transfers', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('amount')->nullable();
            $blueprint->string('purpose')->nullable();
            $blueprint->integer('from_payment_method')->nullable();
            $blueprint->integer('from_bank_name')->nullable();
            $blueprint->integer('to_payment_method')->nullable();
            $blueprint->integer('to_bank_name')->nullable();
            $blueprint->date('transfer_date')->nullable();
            $blueprint->tinyInteger('active_status')->nullable()->default(1);
            $blueprint->timestamps();

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
        Schema::dropIfExists('sm_amount_transfers');
    }
}
