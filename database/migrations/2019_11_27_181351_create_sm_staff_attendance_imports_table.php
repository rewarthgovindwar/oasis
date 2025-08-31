<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStaffAttendanceImportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_staff_attendance_imports', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('attendence_date')->nullable();
            $blueprint->string('in_time', 50)->nullable();
            $blueprint->string('out_time', 50)->nullable();
            $blueprint->string('attendance_type', 10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $blueprint->string('notes', 500)->nullable();
            $blueprint->timestamps();

            $blueprint->integer('staff_id')->nullable()->unsigned();
            $blueprint->foreign('staff_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

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
        Schema::dropIfExists('sm_staff_attendance_imports');
    }
}
