<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_schools', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('school_name', 200)->nullable();
            $blueprint->tinyInteger('created_by')->default(1);
            $blueprint->tinyInteger('updated_by')->default(1);
            $blueprint->string('email', 200)->nullable();
            $blueprint->string('domain', 191)->default('school');
            $blueprint->text('address')->nullable();
            $blueprint->string('phone', 20)->nullable();
            $blueprint->string('school_code', 200)->nullable();
            $blueprint->boolean('is_email_verified')->default(0);
            $blueprint->date('starting_date')->nullable();
            $blueprint->date('ending_date')->nullable();
            $blueprint->integer('package_id')->nullable();
            $blueprint->string('plan_type', 200)->nullable();
            $blueprint->integer('region')->nullable();
            $blueprint->enum('contact_type', ['yearly', 'monthly', 'once'])->nullable();
            $blueprint->tinyInteger('active_status')->default(1)->comment('1 approved, 0 pending');
            $blueprint->string('is_enabled', 20)->default('yes')->comment('yes=Login enable, no=Login disable');
            $blueprint->timestamps();
        });

        DB::table('sm_schools')->insert([
            [
                'school_name' => 'InfixEdu',
                'created_by' => 1,
                'updated_by' => 1,
                'active_status' => 1,
                'is_enabled' => 'yes',
                'email' => 'admin@infixedu.com',
                'starting_date' => date('Y-m-d'),
            ],
        ]);
    }

    // update 6.1 to 6.2
    // update 6.2 to 6.4
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_schools');
    }
}
