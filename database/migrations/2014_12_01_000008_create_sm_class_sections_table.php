<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmClassSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_class_sections', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->index(['class_id', 'section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_class_sections');
    }
}
