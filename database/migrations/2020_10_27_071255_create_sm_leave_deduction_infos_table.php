<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLeaveDeductionInfosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_leave_deduction_infos', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('staff_id')->nullable();
            $blueprint->integer('payroll_id')->nullable();
            $blueprint->integer('extra_leave')->nullable();
            $blueprint->integer('salary_deduct')->nullable();
            $blueprint->string('pay_month')->nullable();
            $blueprint->string('pay_year')->nullable();
            $blueprint->tinyInteger('active_status')->nullable()->default(0);
            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('restrict');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_leave_deduction_infos');
    }
}
