<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOtpCodesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_otp_codes', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->string('otp_code');
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $blueprint->string('expired_time', 200)->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otp_codes');
    }
}
