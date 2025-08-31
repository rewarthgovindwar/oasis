<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speech_sliders', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name')->nullable();
            $blueprint->string('designation')->nullable();
            $blueprint->string('title')->nullable();
            $blueprint->text('speech')->nullable();
            $blueprint->string('image')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });
        $datas = [
            "Principal's Speech",
            "Vice Principal's Speech",
            "Founder's Speech",
        ];
        foreach ($datas as $key => $data) {
            $key++;
            DB::table('speech_sliders')->insert([
                [
                    'name' => fake()->name,
                    'title' => 'Speech From '.$data,
                    'designation' => $data,
                    'speech' => 'Infix Edu is a traditional and reputed school, the students use their talents to develop creative spirit in creating skilled citizens, and the light of education has shown people the way of life.',
                    'image' => sprintf('public/uploads/theme/edulia/speech_slider/speech-%s.jpg', $key),
                    'school_id' => 1,
                ],
            ]);
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('speech_sliders');
    }
};
