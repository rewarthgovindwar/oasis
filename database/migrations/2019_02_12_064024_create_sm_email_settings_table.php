<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmEmailSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_email_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('email_engine_type')->nullable();
            $blueprint->string('from_name')->nullable();
            $blueprint->string('from_email')->nullable();

            $blueprint->string('mail_driver')->nullable();
            $blueprint->string('mail_host')->nullable();
            $blueprint->string('mail_port')->nullable();
            $blueprint->string('mail_username')->nullable();
            $blueprint->string('mail_password')->nullable();
            $blueprint->string('mail_encryption')->nullable();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->tinyInteger('active_status')->default(1);

            $blueprint->timestamps();
        });
        DB::table('sm_email_settings')->insert([
            [
                'email_engine_type' => 'smtp',
                'from_name' => 'System Admin',
                'from_email' => 'hello@aorasoft.com',
                'mail_driver' => 'smtp',
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => '587',
                'mail_username' => 'hello@aorasoft.com',
                'mail_password' => '123456',
                'mail_encryption' => 'tls',
                'active_status' => '0',
                'academic_id' => 1,
            ],
        ]);

        DB::table('sm_email_settings')->insert([
            [
                'email_engine_type' => 'php',
                'from_name' => 'System Admin',
                'from_email' => 'hello@aorasoft.com',
                'mail_driver' => 'php',
                'mail_host' => '',
                'mail_port' => '',
                'mail_username' => '',
                'mail_password' => '',
                'mail_encryption' => '',
                'active_status' => '1',
                'academic_id' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_email_settings');
    }
}
