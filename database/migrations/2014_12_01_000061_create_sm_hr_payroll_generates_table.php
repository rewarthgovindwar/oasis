<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHrPayrollGeneratesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_hr_payroll_generates', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->double('basic_salary')->nullable();
            $blueprint->double('total_earning')->nullable();
            $blueprint->double('total_deduction')->nullable();
            $blueprint->double('gross_salary')->nullable();
            $blueprint->double('tax')->nullable();
            $blueprint->double('net_salary')->nullable();
            $blueprint->string('payroll_month')->nullable();
            $blueprint->string('payroll_year')->nullable();
            $blueprint->string('payroll_status')->nullable()->comment('NG for not generated, G for generated, P for paid');
            $blueprint->string('payment_mode')->nullable();
            $blueprint->date('payment_date')->nullable();
            $blueprint->integer('bank_id')->nullable();
            $blueprint->string('note', 200)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('staff_id')->nullable()->unsigned();
            $blueprint->foreign('staff_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        //  Schema::table('sm_hr_payroll_generates', function($table) {
        //     $table->foreign('staff_id')->references('id')->on('sm_staffs');

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_hr_payroll_generates');
    }
}
