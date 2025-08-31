<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentExcelFormatsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_student_excel_formats', function (Blueprint $blueprint): void {
            $blueprint->string('roll_no')->nullable();
            $blueprint->string('first_name')->nullable();
            $blueprint->string('last_name')->nullable();
            $blueprint->string('date_of_birth')->nullable();
            $blueprint->string('religion')->nullable();
            $blueprint->string('caste')->nullable();
            $blueprint->string('mobile')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('admission_date')->nullable();
            $blueprint->string('category')->nullable();
            $blueprint->string('blood_group')->nullable();
            $blueprint->string('height')->nullable();
            $blueprint->string('weight')->nullable();
            $blueprint->string('siblings_id')->nullable();
            $blueprint->string('father_name')->nullable();
            $blueprint->string('father_phone')->nullable();
            $blueprint->string('father_occupation')->nullable();
            $blueprint->string('mother_name')->nullable();
            $blueprint->string('mother_phone')->nullable();
            $blueprint->string('mother_occupation')->nullable();
            $blueprint->string('guardian_name')->nullable();
            $blueprint->string('guardian_relation')->nullable();
            $blueprint->string('guardian_email')->nullable();
            $blueprint->string('guardian_phone')->nullable();
            $blueprint->string('guardian_occupation')->nullable();
            $blueprint->string('guardian_address')->nullable();
            $blueprint->string('current_address')->nullable();
            $blueprint->string('permanent_address')->nullable();
            $blueprint->string('bank_account_no')->nullable();
            $blueprint->string('bank_name')->nullable();
            $blueprint->string('national_identification_no')->nullable();
            $blueprint->string('local_identification_no')->nullable();
            $blueprint->string('previous_school_details')->nullable();
            $blueprint->string('note')->nullable();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_student_excel_formats');
    }
}
