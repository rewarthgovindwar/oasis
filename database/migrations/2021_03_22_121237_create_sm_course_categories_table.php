<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmCourseCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_course_categories', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('category_name')->nullable();
            $blueprint->text('category_image')->nullable();
            $blueprint->unsignedBigInteger('school_id')->default(1)->unsigned();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_course_categories');
    }
}
