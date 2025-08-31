<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFmFeesGroupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fm_fees_groups', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name', 200)->nullable();
            $blueprint->text('description')->nullable();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_fees_groups');
    }
}
