<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmFeesGroupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_fees_groups', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 200)->nullable();
            $blueprint->string('type', 200)->nullable();
            $blueprint->date('start_date')->nullable();
            $blueprint->date('end_date')->nullable();
            $blueprint->date('due_date')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('un_semester_label_id')->nullable();
        });

        // DB::table('sm_fees_groups')->insert([
        //     [
        //         'name' => 'Transport Fee',
        //         'type' => 'System',
        //         'created_by' => 1,
        //         'created_by' => 1,
        //         'school_id' => 1,
        //         'description' => 'System Automatic created. This fees will come from transport section',
        //     ],
        //     [
        //         'name' => 'Dormitory Fee',
        //         'type' => 'System',
        //         'created_by' => 1,
        //         'created_by' => 1,
        //         'school_id' => 1,
        //         'description' => 'System Automatic created. This fees will come from dormitory section',
        //     ]
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_fees_groups');
    }
}
