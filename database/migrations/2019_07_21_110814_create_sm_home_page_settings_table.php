<?php

use App\SmHomePageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHomePageSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_home_page_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title', 255)->nullable();
            $blueprint->string('long_title', 255)->nullable();
            $blueprint->text('short_description')->nullable();
            $blueprint->string('link_label', 255)->nullable();
            $blueprint->string('link_url', 255)->nullable();
            $blueprint->string('image', 255)->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $smHomePageSetting = new SmHomePageSetting();
        $smHomePageSetting->title = 'THE ULTIMATE EDUCATION ERP';
        $smHomePageSetting->long_title = 'INFIX';
        $smHomePageSetting->short_description = 'Managing various administrative tasks in one place is now quite easy and time savior with this INFIX and Give your valued time to your institute that will increase next generation productivity for our society.';
        $smHomePageSetting->link_label = 'Learn More About Us';
        $smHomePageSetting->link_url = 'http://infixedu.com/about';
        $smHomePageSetting->image = 'public/backEnd/img/client/home-banner1.jpg';
        $smHomePageSetting->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_home_page_settings');
    }
}
