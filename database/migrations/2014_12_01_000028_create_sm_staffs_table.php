<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmStaffsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_staffs', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
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
            $blueprint->string('merital_status', 30)->nullable();
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
            $blueprint->string('metarnity_leave', 15)->nullable();
            $blueprint->string('bank_account_name', 50)->nullable();
            $blueprint->string('bank_account_no', 50)->nullable();
            $blueprint->string('bank_name', 20)->nullable();
            $blueprint->string('bank_brach', 30)->nullable();
            $blueprint->string('facebook_url', 100)->nullable();
            $blueprint->string('twiteer_url', 100)->nullable();
            $blueprint->string('linkedin_url', 100)->nullable();
            $blueprint->string('instragram_url', 100)->nullable();
            $blueprint->string('joining_letter', 500)->nullable();
            $blueprint->string('resume', 500)->nullable();
            $blueprint->string('other_document', 500)->nullable();
            $blueprint->string('notes', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->tinyInteger('show_public')->default(0);
            $blueprint->string('driving_license', 255)->nullable();
            $blueprint->date('driving_license_ex_date')->nullable();
            $blueprint->text('custom_field')->nullable();
            $blueprint->string('custom_field_form_name')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('designation_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('designation_id')->references('id')->on('sm_designations')->onDelete('set null');

            $blueprint->integer('department_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('department_id')->references('id')->on('sm_human_departments')->onDelete('set null');

            $blueprint->integer('user_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('parent_id')->nullable();

            $blueprint->integer('role_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('role_id')->references('id')->on('infix_roles')->onDelete('set null');

            $blueprint->integer('previous_role_id')->nullable();

            $blueprint->integer('gender_id')->nullable()->unsigned()->default(1);
            $blueprint->foreign('gender_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('is_saas')->nullable()->default(0)->unsigned();
        });

        DB::table('sm_staffs')->insert([
            [
                'staff_no' => '1',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'full_name' => 'Super Admin',
                'email' => 'admin@infixedu.com',
                'created_at' => date('Y-m-d h:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_staffs');
    }
}
