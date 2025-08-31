<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentBulkTemporariesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_bulk_temporaries', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('admission_number')->nullable();
            $blueprint->string('roll_no')->nullable();
            $blueprint->string('first_name')->nullable();
            $blueprint->string('last_name')->nullable();
            $blueprint->string('date_of_birth')->nullable();
            $blueprint->string('religion')->nullable();
            $blueprint->string('gender')->nullable();

            $blueprint->string('caste')->nullable();
            $blueprint->string('mobile')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('admission_date')->nullable();
            $blueprint->string('blood_group')->nullable();
            $blueprint->string('height')->nullable();
            $blueprint->string('weight')->nullable();

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
            $blueprint->text('note')->nullable();

            $blueprint->string('user_id')->nullable();

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_bulk_temporaries');
    }
}
