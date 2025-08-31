<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHrPayrollEarnDeducsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_hr_payroll_earn_deducs', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('type_name')->nullable();
            $blueprint->float('amount', 10)->nullable();
            $blueprint->string('earn_dedc_type')->length(5)->nullable()->comment('e for earnings and d for deductions');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('payroll_generate_id')->nullable()->unsigned();
            $blueprint->foreign('payroll_generate_id')->references('id')->on('sm_hr_payroll_generates')->onDelete('cascade');

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
        Schema::dropIfExists('sm_hr_payroll_earn_deducs');
    }
}
