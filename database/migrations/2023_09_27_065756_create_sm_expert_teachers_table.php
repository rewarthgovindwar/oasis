<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_expert_teachers', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->tinyInteger('staff_id');
            $blueprint->tinyInteger('created_by')->nullable();
            $blueprint->tinyInteger('updated_by')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->integer('position')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_expert_teachers');
    }
};
