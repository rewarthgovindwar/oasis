<?php

use App\Models\SmDonor;
use App\SmBaseSetup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_donors', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('full_name', 200)->nullable();
            $blueprint->string('profession', 200)->nullable();
            $blueprint->date('date_of_birth')->nullable();
            $blueprint->string('email', 200)->nullable();
            $blueprint->string('mobile', 200)->nullable();
            $blueprint->string('photo')->nullable();
            $blueprint->string('age', 200)->nullable();
            $blueprint->string('current_address', 500)->nullable();
            $blueprint->string('permanent_address', 500)->nullable();
            $blueprint->tinyInteger('show_public')->default(1);
            $blueprint->text('custom_field')->nullable();
            $blueprint->string('custom_field_form_name')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('bloodgroup_id')->nullable()->unsigned();
            $blueprint->foreign('bloodgroup_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('religion_id')->nullable()->unsigned();
            $blueprint->foreign('religion_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('gender_id')->nullable()->unsigned();
            $blueprint->foreign('gender_id')->references('id')->on('sm_base_setups')->onDelete('set null');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        // Content For Demo Data Start
        $bloodgroup = SmBaseSetup::where('base_group_id', '=', '3')->where('base_setup_name', 'A+')->first();
        $religion = SmBaseSetup::where('base_group_id', '=', '2')->where('base_setup_name', 'Islam')->first();
        $gender = SmBaseSetup::where('base_group_id', '=', '1')->where('base_setup_name', 'Male')->first();
        $datas = [
            ['Abdur Rahman', 'Doctor', date('Y-m-d', strtotime('1990-12-12')), 'abdurrahman@infixedu.com', '+881235854', $bloodgroup->id, $religion->id, $gender->id],
            [' Md Rahim ', 'Farmer', date('Y-m-d', strtotime('1993-08-05')), 'rahim@infixedu.com', '+8855525412', $bloodgroup->id, $religion->id, $gender->id],
            ['Md Malek', 'Engineer', date('Y-m-d', strtotime('1990-12-12')), 'malek@infixedu.com', '+8852526698', $bloodgroup->id, $religion->id, $gender->id],
        ];

        foreach ($datas as $key => $data) {
            $key++;
            $storeData = new SmDonor();
            $storeData->full_name = $data[0];
            $storeData->profession = $data[1];
            $storeData->date_of_birth = $data[2];
            $storeData->email = $data[3];
            $storeData->mobile = $data[4];
            $storeData->bloodgroup_id = $data[5];
            $storeData->religion_id = $data[6];
            $storeData->gender_id = $data[7];
            $storeData->photo = 'public/uploads/theme/edulia/donor/default_donor.jpg';
            $storeData->current_address = 'Dhaka, Bangladesh';
            $storeData->permanent_address = 'Dhaka, Bangladesh';
            $storeData->school_id = 1;
            $storeData->save();
        }

        // Content For Demo Data End
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_donors');
    }
};
