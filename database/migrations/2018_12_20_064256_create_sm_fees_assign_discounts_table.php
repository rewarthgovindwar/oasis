<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmFeesAssignDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_fees_assign_discounts', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('student_id')->nullable()->unsigned();
            $blueprint->foreign('student_id')->references('id')->on('sm_students')->onDelete('cascade');

            $blueprint->integer('record_id')->nullable()->unsigned();

            $blueprint->integer('fees_discount_id')->nullable()->unsigned();
            $blueprint->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('cascade');

            $blueprint->integer('fees_type_id')->nullable()->unsigned();
            // $table->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('cascade');

            $blueprint->integer('fees_group_id')->nullable()->unsigned();
            // $table->foreign('fees_group_id')->references('id')->on('sm_fees_groups')->onDelete('cascade');

            $blueprint->double('applied_amount')->nullable()->default(0);
            $blueprint->double('unapplied_amount')->nullable();

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
        Schema::dropIfExists('sm_fees_assign_discounts');
    }
}
