<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->float('amount')->nullable();
            $blueprint->string('payment_method')->nullable();
            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->integer('bank_id')->nullable();
            $blueprint->string('note')->nullable();
            $blueprint->string('type')->nullable()->comment('diposit, refund, expense, fees_refund');
            $blueprint->text('file')->nullable();
            $blueprint->text('reject_note')->nullable();
            $blueprint->float('expense')->nullable();
            $blueprint->string('status')->default('pending')->comment('pending, approve, reject');
            $blueprint->integer('created_by')->nullable();
            $blueprint->integer('academic_id')->default(1);
            $blueprint->integer('school_id')->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
