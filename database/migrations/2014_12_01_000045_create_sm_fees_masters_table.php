<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmFeesMastersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_fees_masters', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('date')->nullable();

            $blueprint->float('amount', 10)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('fees_group_id')->nullable()->unsigned();
            $blueprint->foreign('fees_group_id')->references('id')->on('sm_fees_groups')->onDelete('cascade');

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            $blueprint->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->integer('un_semester_label_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_fees_masters');
    }
}
