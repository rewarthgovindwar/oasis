<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmItemSellsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_item_sells', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('student_staff_id')->nullable();
            $blueprint->date('sell_date')->nullable();
            $blueprint->string('reference_no', 50)->nullable();
            $blueprint->float('grand_total')->nullable();
            $blueprint->float('total_quantity')->nullable();
            $blueprint->float('total_paid')->nullable();
            $blueprint->float('total_due')->nullable();
            $blueprint->integer('income_head_id')->nullable();
            $blueprint->integer('account_id')->nullable();
            $blueprint->string('payment_method')->nullable();
            $blueprint->string('paid_status')->nullable()->comment('P = paid, PP = partially paid, U = unpaid, R = ----');
            $blueprint->string('description')->length(500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

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
        Schema::dropIfExists('sm_item_sells');
    }
}
