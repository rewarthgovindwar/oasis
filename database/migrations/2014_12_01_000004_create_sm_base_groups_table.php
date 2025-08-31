<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmBaseGroupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_base_groups', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 200);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        DB::table('sm_base_groups')->insert([
            [
                'name' => 'Gender',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Religion',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Blood Group',
                'created_at' => date('Y-m-d h:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_base_groups');
    }
}
