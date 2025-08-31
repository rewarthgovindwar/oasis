<?php

use App\Models\DirectFeesReminder;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectFeesRemindersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('direct_fees_reminders', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('due_date_before');
            $blueprint->string('notification_types');
            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $schools = SmSchool::all();
        foreach ($schools as $school) {
            $data = new DirectFeesReminder();
            $data->due_date_before = 5;
            $data->school_id = $school->id;
            $data->notification_types = '["system"]';
            $data->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_fees_reminders');
    }
}
