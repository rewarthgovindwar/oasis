<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmProductPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_product_purchases', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('purchase_date');
            $blueprint->date('expaire_date');
            $blueprint->float('price', 10)->nullable();
            $blueprint->float('paid_amount', 10)->nullable();
            $blueprint->float('due_amount', 10)->nullable();
            $blueprint->string('package', 10)->nullable();
            $blueprint->timestamps();

            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('staff_id')->nullable()->unsigned();
            $blueprint->foreign('staff_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_product_purchases');
    }
}
