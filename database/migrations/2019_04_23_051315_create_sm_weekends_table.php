<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmWeekendsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_weekends', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name')->nullable();
            $blueprint->integer('order')->nullable();
            $blueprint->integer('is_weekend')->nullable();
            $blueprint->integer('active_status')->default(1);

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->string('created_at')->nullable();
            $blueprint->string('updated_at')->nullable();
            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('set null');
            // $table->timestamps();
        });

        DB::table('sm_weekends')->insert([
            [
                'name' => 'Saturday',
                'order' => 1,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Sunday',
                'order' => 2,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Monday',
                'order' => 3,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Tuesday',
                'order' => 4,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Wednesday',
                'order' => 5,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Thursday',
                'order' => 6,
                'is_weekend' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Friday',
                'order' => 7,
                'is_weekend' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_weekends');
    }
}
