<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_bank_accounts', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('bank_name')->nullable();
            $blueprint->string('account_name')->nullable();
            $blueprint->string('account_number')->nullable();
            $blueprint->string('account_type')->nullable();
            $blueprint->double('opening_balance')->default(0);
            $blueprint->double('current_balance')->default(0);
            $blueprint->text('note')->nullable();
            $blueprint->tinyInteger('active_status')->default(0);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_bank_accounts');
    }
}
