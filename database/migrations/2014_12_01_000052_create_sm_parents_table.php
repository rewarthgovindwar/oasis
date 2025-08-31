<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmParentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_parents', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('fathers_name', 200)->nullable();
            $blueprint->string('fathers_mobile', 200)->nullable();
            $blueprint->string('fathers_occupation', 200)->nullable();
            $blueprint->string('fathers_photo', 200)->nullable();
            $blueprint->string('mothers_name', 200)->nullable();
            $blueprint->string('mothers_mobile', 200)->nullable();
            $blueprint->string('mothers_occupation', 200)->nullable();
            $blueprint->string('mothers_photo', 200)->nullable();
            $blueprint->string('relation', 200)->nullable();
            $blueprint->string('guardians_name', 200)->nullable();
            $blueprint->string('guardians_mobile', 200)->nullable();
            $blueprint->string('guardians_email', 200)->nullable();
            $blueprint->string('guardians_occupation', 200)->nullable();
            $blueprint->string('guardians_relation', 30)->nullable();
            $blueprint->string('guardians_photo', 200)->nullable();
            $blueprint->string('guardians_address', 200)->nullable();
            $blueprint->integer('is_guardian')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('user_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        //   Schema::table('sm_parents', function($table) {
        //      $table->foreign('user_id')->references('id')->on('users');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_parents');
    }
}
