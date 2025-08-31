<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDireFeesInstallmentChildPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dire_fees_installment_child_payments', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('direct_fees_installment_assign_id');
            $blueprint->integer('invoice_no')->default(1);
            $blueprint->float('amount', 10)->nullable();
            $blueprint->float('paid_amount', 10)->nullable();
            $blueprint->float('balance_amount', 10)->nullable();
            $blueprint->date('payment_date')->nullable();
            $blueprint->string('payment_mode', 100)->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->string('slip')->nullable();
            $blueprint->tinyInteger('active_status')->default(0);
            $blueprint->integer('bank_id')->nullable()->unsigned();
            $blueprint->foreign('bank_id')->references('id')->on('sm_bank_accounts')->onDelete('cascade');
            $blueprint->float('discount_amount', 10)->default(0)->nullable();

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            $blueprint->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('record_id')->nullable()->unsigned();

            $blueprint->integer('created_by')->nullable()->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dire_fees_installment_child_payments');
    }
}
