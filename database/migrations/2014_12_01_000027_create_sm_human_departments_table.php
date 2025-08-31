<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmHumanDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_human_departments', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();
            $blueprint->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            //  $table->integer('academic_id')->nullable()->default(1)->unsigned();
            //  $table->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            $blueprint->integer('is_saas')->nullable()->default(0)->unsigned();
        });

        DB::table('sm_human_departments')->insert([
            [
                'name' => 'Admin',
                'created_at' => date('Y-m-d h:i:s'),
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_human_departments');
    }
}
