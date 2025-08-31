<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBankPaymentSlipsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_bank_payment_slips', function (Blueprint $blueprint): void {
            $blueprint->bigIncrements('id');

            $blueprint->date('date');
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('slip')->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->integer('bank_id')->nullable();

            $blueprint->tinyInteger('approve_status')->default(0)->comment('0 pending, 1 approve');
            $blueprint->string('payment_mode')->comment('Bk= bank, Cq=Cheque');
            $blueprint->text('reason')->nullable();

            $blueprint->integer('fees_discount_id')->nullable()->unsigned();
            $blueprint->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('restrict');

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            // $table->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('restrict');
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('class_id')->nullable()->unsigned();
            // $table->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('assign_id')->nullable()->unsigned();
            // $table->foreign('assign_id')->references('id')->on('sm_fees_assigns')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            // $table->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('restrict');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();

            $blueprint->integer('child_payment_id')->nullable();
            $blueprint->integer('installment_id')->nullable();
            // $table->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->timestamps();
            $blueprint->integer('active_status')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_bank_payment_slips');
    }
}
