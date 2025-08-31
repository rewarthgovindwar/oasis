<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_custom_fields', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('form_name');
            $blueprint->string('label');
            $blueprint->string('type');
            $blueprint->string('min_max_length')->nullable();
            $blueprint->string('min_max_value')->nullable();
            $blueprint->string('name_value')->nullable();
            $blueprint->string('width')->nullable();
            $blueprint->tinyInteger('required')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1);
            $blueprint->integer('academic_id')->default(1);
            $blueprint->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_custom_fields');
    }
}
