<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLeaveDefinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_leave_defines', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('days')->nullable()->unsigned();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('type_id')->nullable()->unsigned();
            $blueprint->foreign('type_id')->references('id')->on('sm_leave_types')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('total_days')->nullable()->default(0)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_leave_defines');
    }
}
