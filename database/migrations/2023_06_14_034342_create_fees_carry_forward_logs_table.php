<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_carry_forward_logs', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('student_record_id');
            $blueprint->text('note');
            $blueprint->float('amount');
            $blueprint->string('amount_type');
            $blueprint->integer('created_by')->nullable();
            $blueprint->integer('updated_by')->nullable();
            $blueprint->string('type');
            $blueprint->timestamp('date');
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_carry_forward_logs');
    }
};
