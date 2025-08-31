<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFmFeesTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_fees_transactions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('invoice_number')->nullable();
            $blueprint->integer('student_id')->nullable();
            $blueprint->integer('user_id')->nullable();
            $blueprint->string('payment_method')->nullable();
            $blueprint->integer('bank_id')->nullable();
            $blueprint->float('add_wallet_money')->nullable();
            $blueprint->string('payment_note')->nullable();
            $blueprint->text('file')->nullable();
            $blueprint->string('paid_status')->nullable();
            $blueprint->unsignedBigInteger('fees_invoice_id')->nullable()->unsigned();
            $blueprint->foreign('fees_invoice_id')->references('id')->on('fm_fees_invoices')->onDelete('cascade');
            $blueprint->integer('school_id')->nullable();
            $blueprint->integer('academic_id')->nullable();
            $blueprint->float('service_charge')->nullable();
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_fees_transactions');
    }
}
