<?php

use App\Models\FeesCarryForwardSettings;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_carry_forward_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->integer('fees_due_days');
            $blueprint->string('payment_gateway');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $schools = SmSchool::get();
        foreach ($schools as $school) {
            $storeData = new FeesCarryForwardSettings();
            $storeData->title = 'Fees Carry Forward';
            $storeData->fees_due_days = 60;
            $storeData->payment_gateway = 'Cash';
            $storeData->school_id = $school->id;
            $storeData->save();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_carry_forward_settings');
    }
};
