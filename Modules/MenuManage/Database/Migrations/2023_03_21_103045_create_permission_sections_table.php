<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionSectionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permission_sections', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name')->nullable();
            $blueprint->integer('position')->default(9999);
            $blueprint->integer('user_id')->default(1);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->tinyInteger('saas')->default(0);
            $blueprint->timestamps();
        });

        DB::table('permission_sections')->insert([
            'id' => 1,
            'name' => '',
            'position' => 1,
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_sections');
    }
}
