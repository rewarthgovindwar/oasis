<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignIncidentCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assign_incident_comments', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('user_id')->nullable();
            $blueprint->longText('comment')->nullable();

            $blueprint->integer('incident_id')->unsigned();

            $blueprint->integer('school_id')->default(1)->unsigned();

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_incident_comments');
    }
}
