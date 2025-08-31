<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmCustomTemporaryResultsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_custom_temporary_results', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('student_id')->nullable();
            $blueprint->string('admission_no', 200)->nullable();
            $blueprint->string('full_name', 200)->nullable();
            $blueprint->string('term1', 200)->nullable();
            $blueprint->string('gpa1', 200)->nullable();
            $blueprint->string('term2', 200)->nullable();
            $blueprint->string('gpa2', 200)->nullable();
            $blueprint->string('term3', 200)->nullable();
            $blueprint->string('gpa3', 200)->nullable();
            $blueprint->string('final_result', 200)->nullable();
            $blueprint->string('final_grade', 200)->nullable();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('restrict');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_custom_temporary_results');
    }
}
