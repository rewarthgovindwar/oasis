<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFmFeesInvoiceChieldsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_fees_invoice_chields', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('fees_invoice_id')->nullable()->unsigned();
            $blueprint->foreign('fees_invoice_id')->references('id')->on('fm_fees_invoices')->onDelete('cascade');
            $blueprint->integer('fees_type')->nullable();
            $blueprint->float('amount')->nullable();
            $blueprint->float('weaver')->nullable();
            $blueprint->float('fine')->nullable();
            $blueprint->float('sub_total')->nullable();
            $blueprint->float('paid_amount')->nullable();
            $blueprint->float('service_charge')->nullable();
            $blueprint->float('due_amount')->nullable();
            $blueprint->string('note')->nullable();
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
        Schema::dropIfExists('fm_fees_invoice_chields');
    }
}
