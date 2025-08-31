<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLeaveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_leave_requests', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->date('apply_date')->nullable();
            $blueprint->date('leave_from')->nullable();
            $blueprint->date('leave_to')->nullable();
            $blueprint->text('reason')->nullable();
            $blueprint->text('note')->nullable();
            $blueprint->string('file')->nullable();
            $blueprint->string('approve_status')->nullable()->comment('P for Pending, A for Approve, R for reject');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('leave_define_id')->nullable()->unsigned();
            $blueprint->foreign('leave_define_id')->references('id')->on('sm_leave_defines')->onDelete('cascade');

            $blueprint->integer('staff_id')->nullable()->unsigned();
            $blueprint->foreign('staff_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $blueprint->integer('type_id')->nullable()->unsigned();
            $blueprint->foreign('type_id')->references('id')->on('sm_leave_types')->onDelete('cascade');

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
        Schema::dropIfExists('sm_leave_requests');
    }
}
