<?php

use App\SmLibraryMember;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLibraryMembersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_library_members', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('member_ud_id')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('member_type')->nullable()->unsigned();
            $blueprint->foreign('member_type')->references('id')->on('roles')->onDelete('cascade');

            $blueprint->integer('student_staff_id')->nullable()->unsigned();
            $blueprint->foreign('student_staff_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        // $member_ud_id =['1001','2001','3001','5001'];
        // $member_type =['2','2','4','8'];
        // $student_staff_id =['2','14','6','4'];

        //  for($i=0; $i<4; $i++){
        //     $store = new SmLibraryMember();
        //     $store->member_ud_id = $member_ud_id[$i];
        //     $store->member_type =$member_type[$i];
        //     $store->student_staff_id =$student_staff_id[$i];
        //     $store->save();
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_library_members');
    }
}
