<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmFeesPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('sm_fees_payments', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('discount_month')->nullable();
            $blueprint->double('discount_amount')->nullable();
            $blueprint->double('fine')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->date('payment_date')->nullable();
            $blueprint->string('payment_mode', 100)->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->string('slip')->nullable();
            $blueprint->string('fine_title')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('assign_id')->nullable()->unsigned();
            $blueprint->foreign('assign_id')->references('id')->on('sm_fees_assigns')->onDelete('cascade');

            $blueprint->integer('bank_id')->nullable()->unsigned();
            $blueprint->foreign('bank_id')->references('id')->on('sm_bank_accounts')->onDelete('cascade');

            $blueprint->integer('fees_discount_id')->nullable()->unsigned();
            $blueprint->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('cascade');

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            $blueprint->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('cascade');
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('direct_fees_installment_assign_id')->nullable()->unsigned();
            $blueprint->integer('installment_payment_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_fees_payments');
    }
}
