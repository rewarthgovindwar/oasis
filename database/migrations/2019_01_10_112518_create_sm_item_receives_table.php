<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmItemReceivesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_item_receives', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('receive_date')->nullable();
            $blueprint->string('reference_no')->nullable();
            $blueprint->float('grand_total')->nullable();
            $blueprint->float('total_quantity')->nullable();
            $blueprint->float('total_paid')->nullable();
            $blueprint->float('total_due')->nullable();
            $blueprint->integer('expense_head_id')->nullable();
            $blueprint->integer('account_id')->nullable();
            $blueprint->string('payment_method')->nullable();
            $blueprint->string('paid_status')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('supplier_id')->nullable()->unsigned();
            $blueprint->foreign('supplier_id')->references('id')->on('sm_suppliers')->onDelete('cascade');

            $blueprint->integer('store_id')->nullable()->unsigned();
            $blueprint->foreign('store_id')->references('id')->on('sm_item_stores')->onDelete('cascade');

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
        Schema::dropIfExists('sm_item_receives');
    }
}
