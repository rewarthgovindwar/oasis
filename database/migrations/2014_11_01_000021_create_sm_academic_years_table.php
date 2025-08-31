<?php

use App\SmAcademicYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmAcademicYearsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_academic_years', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('year', 200);
            $blueprint->string('title', 200);
            $blueprint->date('starting_date');
            $blueprint->date('ending_date');
            $blueprint->string('copy_with_academic_year')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->string('created_at')->nullable();
            $blueprint->string('updated_at')->nullable();
            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        $year = date('Y');
        $starting_date = $year.'-01-01';
        $ending_date = $year.'-12-30';
        $smAcademicYear = new SmAcademicYear();
        $smAcademicYear->year = $year;
        $smAcademicYear->title = 'Jan-Dec';
        $smAcademicYear->starting_date = $starting_date;
        $smAcademicYear->ending_date = $ending_date;
        $smAcademicYear->created_at = $year.'-01-01';
        $smAcademicYear->updated_at = $year.'-01-01';
        $smAcademicYear->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // \Schema::dropIfExists('sm_academic_years');
    }
}
