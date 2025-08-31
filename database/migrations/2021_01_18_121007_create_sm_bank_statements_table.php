<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_bank_statements', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('bank_id')->nullable();
            $blueprint->integer('after_balance')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('type')->length(11)->nullable()->comment('1 for Income 0 for Expense');

            $blueprint->integer('payment_method')->nullable()->unsigned();

            $blueprint->string('details')->length(500)->nullable();
            $blueprint->integer('item_receive_id')->nullable();
            $blueprint->integer('item_receive_bank_statement_id')->nullable();
            $blueprint->integer('item_sell_bank_statement_id')->nullable();
            $blueprint->integer('item_sell_id')->nullable();
            $blueprint->date('payment_date')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();

            $blueprint->integer('academic_id')->nullable()->unsigned();

            $blueprint->integer('fees_payment_id')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_bank_statements');
    }
}
