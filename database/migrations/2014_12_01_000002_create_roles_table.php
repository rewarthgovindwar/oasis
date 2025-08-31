<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 100)->nullable();
            $blueprint->string('type')->default('System');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->string('created_by')->nullable()->default(1);
            $blueprint->string('updated_by')->nullable()->default(1);
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        DB::table('roles')->insert([
            [
                'name' => 'Super admin',    //      1
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Student',    //      2
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Parents',    //      3
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Teacher',    //      4
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Admin',    //      5
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Accountant',    //      6
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Receptionist',    //      7
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Librarian',    //      8
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],
            [
                'name' => 'Driver',    //      9
                'type' => 'System',
                'school_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
            ],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}
