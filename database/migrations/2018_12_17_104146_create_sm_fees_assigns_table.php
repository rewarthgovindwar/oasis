<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmFeesAssignsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_fees_assigns', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();
            $blueprint->float('fees_amount', 10)->nullable();
            $blueprint->float('applied_discount', 10)->nullable();
            $blueprint->integer('fees_master_id')->nullable()->unsigned();
            $blueprint->foreign('fees_master_id')->references('id')->on('sm_fees_masters')->onDelete('cascade');
            $blueprint->integer('fees_discount_id')->nullable()->unsigned();
            $blueprint->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('cascade');
            $blueprint->integer('record_id')->nullable()->unsigned();
            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->integer('section_id')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_fees_assigns');
    }
}
