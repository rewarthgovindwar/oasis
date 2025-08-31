<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectFeesInstallmentAssignsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('direct_fees_installment_assigns', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('fees_installment_id');
            $blueprint->text('fees_master_ids')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->float('paid_amount', 10)->nullable();
            $blueprint->date('due_date')->nullable();
            $blueprint->date('payment_date')->nullable();
            $blueprint->string('payment_mode', 100)->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->string('slip')->nullable();
            $blueprint->tinyInteger('active_status')->default(0);

            $blueprint->text('assign_ids')->nullable();

            $blueprint->integer('bank_id')->nullable()->unsigned();
            $blueprint->foreign('bank_id')->references('id')->on('sm_bank_accounts')->onDelete('cascade');

            $blueprint->float('discount_amount', 10)->default(0)->nullable();

            $blueprint->integer('fees_discount_id')->nullable()->unsigned();
            $blueprint->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('cascade');

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            $blueprint->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('cascade');

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('collected_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();

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
        Schema::dropIfExists('direct_fees_installment_assigns');
    }
}
