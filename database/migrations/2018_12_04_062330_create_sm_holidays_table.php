<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_holidays', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('holiday_title', 200)->nullable();
            $blueprint->string('details', 500)->nullable();
            $blueprint->date('from_date')->nullable();
            $blueprint->date('to_date')->nullable();
            $blueprint->string('upload_image_file', 200)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        // DB::table('sm_holidays')->insert([
        //     [
        //         'holiday_title'=>'Summer Vacation',
        //         'from_date'=>'2019-05-02',
        //         'to_date'=>'2019-05-08',
        //     ],
        //     [
        //         'holiday_title'=>'Public Holiday',
        //         'from_date'=>'2019-05-010',
        //         'to_date'=>'2019-05-11',
        //     ],
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_holidays');
    }
}
