<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffImportBulkTemporariesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_import_bulk_temporaries', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('staff_no')->nullable();
            $blueprint->string('first_name', 100)->nullable();
            $blueprint->string('last_name', 100)->nullable();
            $blueprint->string('full_name', 200)->nullable();
            $blueprint->string('fathers_name', 100)->nullable();
            $blueprint->string('mothers_name', 100)->nullable();
            $blueprint->date('date_of_birth')->nullable()->default(date('Y-m-d'));
            $blueprint->date('date_of_joining')->nullable()->default(date('Y-m-d'));
            $blueprint->string('email', 50)->nullable();
            $blueprint->string('mobile', 50)->nullable();
            $blueprint->string('emergency_mobile', 50)->nullable();
            $blueprint->string('marital_status', 30)->nullable();
            $blueprint->string('staff_photo')->nullable();
            $blueprint->string('current_address', 500)->nullable();
            $blueprint->string('permanent_address', 500)->nullable();
            $blueprint->string('qualification', 200)->nullable();
            $blueprint->string('experience', 200)->nullable();
            $blueprint->string('epf_no', 20)->nullable();
            $blueprint->string('basic_salary', 200)->nullable();
            $blueprint->string('contract_type', 200)->nullable();
            $blueprint->string('location', 50)->nullable();
            $blueprint->string('casual_leave', 15)->nullable();
            $blueprint->string('medical_leave', 15)->nullable();
            $blueprint->string('maternity_leave', 15)->nullable();
            $blueprint->string('bank_account_name', 50)->nullable();
            $blueprint->string('bank_account_no', 50)->nullable();
            $blueprint->string('bank_name', 20)->nullable();
            $blueprint->string('bank_brach', 30)->nullable();
            $blueprint->string('facebook_url', 100)->nullable();
            $blueprint->string('twitter_url', 100)->nullable();
            $blueprint->string('linkedin_url', 100)->nullable();
            $blueprint->string('instagram_url', 100)->nullable();
            $blueprint->string('joining_letter', 500)->nullable();
            $blueprint->string('resume', 500)->nullable();
            $blueprint->string('other_document', 500)->nullable();
            $blueprint->string('notes', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->string('driving_license', 255)->nullable();
            $blueprint->date('driving_license_ex_date')->nullable();
            $blueprint->string('role')->nullable();
            $blueprint->string('department')->nullable();
            $blueprint->string('designation')->nullable();
            $blueprint->integer('gender_id')->nullable();
            $blueprint->integer('user_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $blueprint->integer('parent_id')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_import_bulk_temoraries');
    }
}
