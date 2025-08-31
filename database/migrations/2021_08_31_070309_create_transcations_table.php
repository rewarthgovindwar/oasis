<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transcations', function (Blueprint $blueprint): void {
            $blueprint->integer('id');
            $blueprint->text('title')->nullable();
            $blueprint->string('type', 20)->default('debit');
            $blueprint->string('payment_method', 20)->nullable();
            $blueprint->string('reference', 20)->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->unsignedBigInteger('morphable_id')->nullable();
            $blueprint->string('morphable_type')->nullable();
            $blueprint->bigInteger('amount')->default(0);
            $blueprint->date('transaction_date')->nullable();
            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->on('users')->references('id')->onDelete('set null');
            $blueprint->integer('school_id')->default(1);
            $blueprint->integer('academic_id')->default(1);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcations');
    }
}
