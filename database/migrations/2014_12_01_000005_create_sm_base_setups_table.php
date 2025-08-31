<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmBaseSetupsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_base_setups', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('base_setup_name', 255);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('base_group_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('base_group_id')->references('id')->on('sm_base_groups')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        DB::table('sm_base_setups')->insert([
            [
                'base_group_id' => 1,
                'base_setup_name' => 'Male',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 1,
                'base_setup_name' => 'Female',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 1,
                'base_setup_name' => 'Others',
                'created_at' => date('Y-m-d h:i:s'),
            ],

            [
                'base_group_id' => 2,
                'base_setup_name' => 'Islam',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 2,
                'base_setup_name' => 'Hinduism',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 2,
                'base_setup_name' => 'Sikhism',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 2,
                'base_setup_name' => 'Buddhism',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 2,
                'base_setup_name' => 'Protestantism',
                'created_at' => date('Y-m-d h:i:s'),
            ],

            [
                'base_group_id' => 3,
                'base_setup_name' => 'A+',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'O+',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'B+',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'AB+',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'A-',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'O-',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'B-',
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'base_group_id' => 3,
                'base_setup_name' => 'AB-',
                'created_at' => date('Y-m-d h:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_base_setups');
    }
}
