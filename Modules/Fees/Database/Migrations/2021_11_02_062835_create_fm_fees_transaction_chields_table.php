<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFmFeesTransactionChieldsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_fees_transaction_chields', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('fees_type')->nullable();
            $blueprint->float('paid_amount')->nullable();
            $blueprint->float('service_charge')->nullable();
            $blueprint->float('fine')->nullable();
            $blueprint->float('weaver')->nullable();
            $blueprint->string('note')->nullable();
            $blueprint->unsignedBigInteger('fees_transaction_id')->nullable()->unsigned();
            $blueprint->foreign('fees_transaction_id')->references('id')->on('fm_fees_transactions')->onDelete('cascade');
            $blueprint->integer('school_id')->nullable();
            $blueprint->integer('academic_id')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_fees_transaction_chields');
    }
}
