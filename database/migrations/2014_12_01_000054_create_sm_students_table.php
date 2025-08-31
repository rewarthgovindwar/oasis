<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_students', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('admission_no')->nullable();
            $blueprint->integer('roll_no')->nullable();
            $blueprint->string('first_name', 200)->nullable();
            $blueprint->string('last_name', 200)->nullable();
            $blueprint->string('full_name', 200)->nullable();
            $blueprint->date('date_of_birth')->nullable();

            $blueprint->string('caste', 200)->nullable();
            $blueprint->string('email', 200)->nullable();
            $blueprint->string('mobile', 200)->nullable();
            $blueprint->date('admission_date')->nullable();
            $blueprint->string('student_photo')->nullable();

            $blueprint->string('age', 200)->nullable();
            $blueprint->string('height', 200)->nullable();
            $blueprint->string('weight', 200)->nullable();
            $blueprint->string('current_address', 500)->nullable();
            $blueprint->string('permanent_address', 500)->nullable();

            $blueprint->string('driver_id', 200)->nullable();
            $blueprint->string('national_id_no', 200)->nullable();
            $blueprint->string('local_id_no', 200)->nullable();
            $blueprint->string('bank_account_no', 200)->nullable();
            $blueprint->string('bank_name', 200)->nullable();
            $blueprint->string('previous_school_details', 500)->nullable();
            $blueprint->text('aditional_notes')->nullable();
            $blueprint->string('ifsc_code', 50)->nullable();
            $blueprint->string('document_title_1', 200)->nullable();
            $blueprint->string('document_file_1', 200)->nullable();
            $blueprint->string('document_title_2', 200)->nullable();
            $blueprint->string('document_file_2', 200)->nullable();
            $blueprint->string('document_title_3', 200)->nullable();
            $blueprint->string('document_file_3', 200)->nullable();
            $blueprint->string('document_title_4', 200)->nullable();
            $blueprint->string('document_file_4', 200)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->text('custom_field')->nullable();
            $blueprint->string('custom_field_form_name')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('bloodgroup_id')->nullable()->unsigned();
            $blueprint->foreign('bloodgroup_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('religion_id')->nullable()->unsigned();
            $blueprint->foreign('religion_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('route_list_id')->nullable()->unsigned();
            $blueprint->foreign('route_list_id')->references('id')->on('sm_routes')->onDelete('set null');

            $blueprint->integer('dormitory_id')->nullable()->unsigned();
            $blueprint->foreign('dormitory_id')->references('id')->on('sm_dormitory_lists')->onDelete('set null');

            $blueprint->integer('vechile_id')->nullable()->unsigned();
            $blueprint->foreign('vechile_id')->references('id')->on('sm_vehicles')->onDelete('set null');

            $blueprint->integer('room_id')->nullable()->unsigned();
            $blueprint->foreign('room_id')->references('id')->on('sm_room_lists')->onDelete('set null');

            $blueprint->integer('student_category_id')->nullable()->unsigned();
            $blueprint->foreign('student_category_id')->references('id')->on('sm_student_categories')->onDelete('set null');

            $blueprint->integer('student_group_id')->nullable()->unsigned();
            $blueprint->foreign('student_group_id')->references('id')->on('sm_student_groups')->onDelete('set null');

            $blueprint->integer('class_id')->nullable()->unsigned();
            $blueprint->foreign('class_id')->references('id')->on('sm_classes')->onDelete('set null');

            $blueprint->integer('section_id')->nullable()->unsigned();
            $blueprint->foreign('section_id')->references('id')->on('sm_sections')->onDelete('set null');

            $blueprint->integer('session_id')->nullable()->unsigned();
            $blueprint->foreign('session_id')->references('id')->on('sm_academic_years')->onDelete('set null');

            $blueprint->integer('parent_id')->nullable()->nullable()->unsigned();
            $blueprint->foreign('parent_id')->references('id')->on('sm_parents')->onDelete('set null');

            $blueprint->integer('user_id')->nullable()->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('infix_roles')->onDelete('cascade');

            $blueprint->integer('gender_id')->nullable()->unsigned();
            $blueprint->foreign('gender_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sm_students', function (Blueprint $blueprint): void {
            $blueprint->dropForeign(['bloodgroup_id']);
            $blueprint->dropForeign(['religion_id']);
            $blueprint->dropForeign(['route_list_id']);
            $blueprint->dropForeign(['dormitory_id']);
            $blueprint->dropForeign(['vechile_id']);
            $blueprint->dropForeign(['room_id']);
            $blueprint->dropForeign(['student_category_id']);
            $blueprint->dropForeign(['student_group_id']);
            $blueprint->dropForeign(['class_id']);
            $blueprint->dropForeign(['section_id']);
            $blueprint->dropForeign(['session_id']);
            $blueprint->dropForeign(['parent_id']);
            $blueprint->dropForeign(['user_id']);
            $blueprint->dropForeign(['role_id']);
            $blueprint->dropForeign(['gender_id']);
            $blueprint->dropForeign(['school_id']);
            $blueprint->dropForeign(['academic_id']);
        });
        Schema::dropIfExists('sm_students');
    }
}
