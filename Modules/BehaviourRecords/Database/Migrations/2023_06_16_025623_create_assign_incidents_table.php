<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assign_incidents', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('point')->nullable();

            $blueprint->integer('incident_id')->unsigned();

            $blueprint->integer('record_id')->unsigned();

            $blueprint->integer('student_id')->nullable()->unsigned();

            $blueprint->integer('added_by')->unsigned();

            $blueprint->integer('academic_id')->nullable()->unsigned();

            $blueprint->integer('school_id')->default(1)->unsigned();

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_incidents');
    }
}
