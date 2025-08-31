<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_payments', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('sm_hr_payroll_generate_id')->nullable()->unsigned();
            $blueprint->foreign('sm_hr_payroll_generate_id')->references('id')->on('sm_hr_payroll_generates');
            $blueprint->double('amount')->nullable();
            $blueprint->string('payment_mode')->nullable();
            $blueprint->integer('payment_method_id')->nullable()->unsigned();
            $blueprint->date('payment_date')->nullable();
            $blueprint->integer('bank_id')->nullable();
            $blueprint->string('note', 200)->nullable();
            $blueprint->integer('created_by')->nullable();
            $blueprint->timestamps();
        });
        Schema::table('sm_hr_payroll_generates', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_hr_payroll_generates', 'is_partial')) {
                $blueprint->integer('is_partial')->after('active_status')->nullable();
            }

            if (! Schema::hasColumn('sm_hr_payroll_generates', 'paid_amount')) {
                $blueprint->integer('paid_amount')->after('active_status')->nullable();
            }
        });
        Schema::table('sm_add_expenses', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_add_expenses', 'payroll_payment_id')) {
                $blueprint->integer('payroll_payment_id')->nullable();
            }
        });
        Schema::table('sm_bank_statements', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('sm_bank_statements', 'payroll_payment_id')) {
                $blueprint->integer('payroll_payment_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
}
