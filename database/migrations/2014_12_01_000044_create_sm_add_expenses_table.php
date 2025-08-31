<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmAddExpensesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_add_expenses', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 255)->nullable();
            $blueprint->date('date')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('file')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->integer('item_receive_id')->nullable();
            $blueprint->integer('inventory_id')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('expense_head_id')->nullable()->unsigned();
            // $table->foreign('expense_head_id')->references('id')->on('sm_expense_heads')->onDelete('cascade');

            $blueprint->integer('account_id')->nullable()->unsigned();
            // $table->foreign('account_id')->references('id')->on('sm_bank_accounts')->onDelete('cascade');

            $blueprint->integer('payment_method_id')->nullable()->unsigned();
            // $table->foreign('payment_method_id')->references('id')->on('sm_payment_methhods')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

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
        Schema::dropIfExists('sm_add_expenses');
    }
}
