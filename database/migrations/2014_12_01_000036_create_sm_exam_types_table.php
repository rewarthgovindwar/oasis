<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmExamTypesTable extends Migration
{
    public function up(): void
    {
        Schema::create('sm_exam_types', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->Integer('active_status')->default(1);
            $blueprint->string('title', 255);
            $blueprint->tinyInteger('is_average')->default(0);
            $blueprint->float('percentage')->nullable();
            $blueprint->float('average_mark')->default(0);
            $blueprint->timestamps();
            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('parent_id')->nullable()->default(0)->unsigned();
        });

        // DB::table('sm_exam_types')->insert([

        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'First Term'
        //     ],
        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Second Term'
        //     ],
        //     [
        //         'school_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Third Term'
        //     ],

        //    ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_exam_types');
    }
}
