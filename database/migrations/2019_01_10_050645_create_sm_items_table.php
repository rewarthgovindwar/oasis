<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_items', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('item_name', 100)->nullable();
            $blueprint->float('total_in_stock')->nullable();
            $blueprint->string('description', 500)->nullable();
            $blueprint->timestamps();

            $blueprint->integer('item_category_id')->nullable()->unsigned();
            $blueprint->foreign('item_category_id')->references('id')->on('sm_item_categories')->onDelete('cascade');

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
        Schema::dropIfExists('sm_items');
    }
}
