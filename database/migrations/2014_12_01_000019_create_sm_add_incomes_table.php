<?php

use App\SmAddIncome;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmAddIncomesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_add_incomes', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 255)->nullable();
            $blueprint->date('date')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('file')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->integer('item_sell_id')->nullable();
            $blueprint->integer('fees_collection_id')->nullable();
            $blueprint->integer('inventory_id')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('income_head_id')->nullable()->unsigned();

            $blueprint->integer('account_id')->nullable()->unsigned();
            $blueprint->foreign('account_id')->references('id')->on('sm_bank_accounts')->onDelete('cascade');

            $blueprint->integer('payment_method_id')->nullable()->unsigned();
            $blueprint->foreign('payment_method_id')->references('id')->on('sm_payment_methhods')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->integer('installment_payment_id')->nullable()->unsigned();
        });

        // $store = new SmAddIncome();
        // $store->name                =           'Donation for Boys football match';
        // $store->income_head_id     =           1;
        // $store->payment_method_id   =           1;
        // $store->date                =           '2019-05-05';
        // $store->amount              =           1200;
        // $store->save();

        // $store = new SmAddIncome();
        // $store->name                =           'Product Sales';
        // $store->income_head_id     =           2;
        // $store->payment_method_id   =           1;
        // $store->date                =           '2019-05-05';
        // $store->amount              =           15000;
        // $store->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_add_incomes');
    }
}
