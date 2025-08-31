<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmModulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_modules', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->integer('order');
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

        });

        $modules = ['Dashboard', 'Admin Section', 'Student Information', 'Teacher', 'Fees Collection', 'Accounts', 'Human resource', 'Leave Application', 'Examination', 'Academics', 'HomeWork', 'Communicate', 'Library', 'Inventory', 'Transport', 'Dormitory', 'Reports', 'System Settings', 'Common', 'Lesson'];

        $count = 0;
        foreach ($modules as $module) {
            DB::table('sm_modules')->insert([
                [
                    'name' => $module,
                    'order' => $count++,
                    'created_at' => date('Y-m-d h:i:s'),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_modules');
    }
}
