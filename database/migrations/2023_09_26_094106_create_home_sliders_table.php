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
        Schema::create('home_sliders', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('image');
            $blueprint->string('link')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        DB::table('home_sliders')->insert([
            [
                'image' => 'public/uploads/theme/edulia/home_slider/banner1.jpeg',
                'link' => 'home',
            ],
            [
                'image' => 'public/uploads/theme/edulia/home_slider/banner2.jpeg',
                'link' => 'home',
            ],
            [
                'image' => 'public/uploads/theme/edulia/home_slider/banner3.jpeg',
                'link' => 'home',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sliders');
    }
};
