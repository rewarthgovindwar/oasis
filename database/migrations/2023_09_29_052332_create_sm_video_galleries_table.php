<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_video_galleries', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->text('video_link')->nullable();
            $blueprint->boolean('is_publish')->default(true);
            $blueprint->integer('position')->default(0);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        DB::table('sm_video_galleries')->insert([
            [
                'name' => 'Science Fair',
                'description' => 'A showcase of student experiments and scientific discoveries',
                'video_link' => 'https://www.youtube.com/watch?v=4zR-uaZjZ2U',
                'position' => 1,
            ],
            [
                'name' => 'Cultural Carnival',
                'description' => 'A lively celebration of diverse traditions, arts, and festivities.',
                'video_link' => 'https://www.youtube.com/watch?v=k61cLi1_Zd0&ab_channel=Infixdev',
                'position' => 2,
            ],
            [
                'name' => 'Student Leadership Summit',
                'description' => 'Empowering future leaders through collaboration and inspiration.',
                'video_link' => 'https://www.youtube.com/watch?v=4zR-uaZjZ2U&ab_channel=Infixdev',
                'position' => 3,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_video_galleries');
    }
};
