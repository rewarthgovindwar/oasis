<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmBackgroundSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_background_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title', 255)->nullable();
            $blueprint->string('type', 255)->nullable();
            $blueprint->string('image', 255)->nullable();
            $blueprint->string('color', 255)->nullable();
            $blueprint->integer('is_default')->default(0);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        DB::table('sm_background_settings')->insert([
            [
                'id' => 1,
                'title' => 'Dashboard Background',
                'type' => 'image',
                'image' => 'public/backEnd/img/body-bg.jpg',
                'color' => '',
                'is_default' => 1,

            ],

            [
                'id' => 2,
                'title' => 'Login Background',
                'type' => 'image',
                'image' => 'public/backEnd/img/login-bg.jpg',
                'color' => '',
                'is_default' => 0,

            ],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_background_settings');
    }
}
