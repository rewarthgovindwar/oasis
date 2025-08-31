<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTeacherEvaluationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_evaluation_settings', function (Blueprint $blueprint): void {
            $blueprint->id();

            $blueprint->boolean('is_enable')->default(0);

            $blueprint->string('submitted_by')->default('[]');

            $blueprint->string('rating_submission_time')->default('any');

            $blueprint->boolean('auto_approval')->default(1);

            $blueprint->date('from_date')->nullable();

            $blueprint->date('to_date')->nullable();

            $blueprint->integer('school_id')->default(1)->unsigned();

            $blueprint->timestamps();
        });

        DB::table('teacher_evaluation_settings')->insert([
            [
                'is_enable' => 0,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluation_settings');
    }
}
