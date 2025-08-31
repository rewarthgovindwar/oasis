<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmItemSellChildrenTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_item_sell_children', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('sell_price')->nullable();
            $blueprint->integer('quantity')->nullable();
            $blueprint->integer('sub_total')->nullable();
            $blueprint->string('description')->length('500')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('item_sell_id')->nullable()->unsigned();
            // $table->foreign('item_sell_id')->references('id')->on('sm_item_sells')->onDelete('cascade');

            $blueprint->integer('item_id')->nullable()->unsigned();
            // $table->foreign('item_id')->references('id')->on('sm_items')->onDelete('cascade');

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
        Schema::dropIfExists('sm_item_sell_children');
    }
}
