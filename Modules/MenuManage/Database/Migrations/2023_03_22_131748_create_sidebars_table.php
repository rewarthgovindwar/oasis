<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSidebarsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('sidebars');
        Schema::create('sidebars', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('permission_id')->nullable();
            $blueprint->integer('position')->nullable();
            $blueprint->integer('section_id')->nullable()->default(1);
            $blueprint->integer('parent')->nullable();
            $blueprint->integer('parent_route')->nullable();
            $blueprint->integer('level')->nullable()->comment('1=paren, 2=child, 3=sub-child');
            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->tinyInteger('is_saas')->default(0);
            $blueprint->integer('ignore')->default(0);
            $blueprint->integer('role_id')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebars');
    }
}
