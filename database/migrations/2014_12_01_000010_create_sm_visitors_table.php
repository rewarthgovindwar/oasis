<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_visitors', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 255);
            $blueprint->string('phone', 255)->nullable();
            $blueprint->string('visitor_id', 255)->nullable();
            $blueprint->integer('no_of_person')->nullable();
            $blueprint->string('purpose', 255)->nullable();
            $blueprint->date('date')->nullable();
            $blueprint->string('in_time', 255)->nullable();
            $blueprint->string('out_time', 255)->nullable();
            $blueprint->string('file', 255)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

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
        Schema::dropIfExists('sm_visitors');
    }
}
