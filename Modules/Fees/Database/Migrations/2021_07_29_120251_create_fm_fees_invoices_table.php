<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFmFeesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_fees_invoices', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('invoice_id');
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');
            $blueprint->integer('class_id')->nullable();
            $blueprint->date('create_date')->nullable();
            $blueprint->date('due_date')->nullable();
            $blueprint->string('payment_status')->nullable();
            $blueprint->string('payment_method')->nullable();
            $blueprint->integer('bank_id')->nullable();
            $blueprint->string('type')->default('fees')->nullable()->comment('fees, lms');
            $blueprint->integer('school_id')->nullable();
            $blueprint->integer('academic_id')->nullable();
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_fees_invoices');
    }
}
