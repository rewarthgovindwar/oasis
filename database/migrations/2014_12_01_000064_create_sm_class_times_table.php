<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmClassTimesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_class_times', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->enum('type', ['exam', 'class'])->nullable();
            $blueprint->string('period')->nullable();
            $blueprint->time('start_time')->nullable();
            $blueprint->time('end_time')->nullable();
            $blueprint->tinyInteger('is_break')->nullable()->comment('1 = tiffin time, 0 = class');
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_class_times');
    }
}
