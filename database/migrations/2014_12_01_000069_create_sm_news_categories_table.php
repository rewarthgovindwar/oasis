<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmNewsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_news_categories', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('category_name');
            $blueprint->string('type')->default('news');
            $blueprint->timestamps();

            $blueprint->unsignedBigInteger('school_id')->default(1)->unsigned();
            // $table->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

        });

        DB::table('sm_news_categories')->insert([
            [
                'category_name' => 'International',    //      1
                'school_id' => '1',
                'type' => 'news',
            ],
            [
                'category_name' => 'Our history',   //      3
                'school_id' => '1',
                'type' => 'history',
            ],
            [
                'category_name' => 'Our mission and vision',   //      3
                'school_id' => '1',
                'type' => 'mission',
            ],
            [
                'category_name' => 'National',   //      2
                'school_id' => '1',
                'type' => 'news',

            ],
            [
                'category_name' => 'Sports',   //      3
                'school_id' => '1',
                'type' => 'news',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_news_categories');
    }
}
