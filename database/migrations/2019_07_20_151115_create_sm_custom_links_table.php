<?php

use App\SmCustomLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmCustomLinksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_custom_links', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title1', 255)->nullable();
            $blueprint->string('title2', 255)->nullable();
            $blueprint->string('title3', 255)->nullable();
            $blueprint->string('title4', 255)->nullable();

            $blueprint->string('link_label1', 255)->nullable();
            $blueprint->string('link_href1', 255)->nullable();
            $blueprint->string('link_label2', 255)->nullable();
            $blueprint->string('link_href2', 255)->nullable();
            $blueprint->string('link_label3', 255)->nullable();
            $blueprint->string('link_href3', 255)->nullable();
            $blueprint->string('link_label4', 255)->nullable();
            $blueprint->string('link_href4', 255)->nullable();

            $blueprint->string('link_label5', 255)->nullable();
            $blueprint->string('link_href5', 255)->nullable();
            $blueprint->string('link_label6', 255)->nullable();
            $blueprint->string('link_href6', 255)->nullable();
            $blueprint->string('link_label7', 255)->nullable();
            $blueprint->string('link_href7', 255)->nullable();
            $blueprint->string('link_label8', 255)->nullable();
            $blueprint->string('link_href8', 255)->nullable();

            $blueprint->string('link_label9', 255)->nullable();
            $blueprint->string('link_href9', 255)->nullable();
            $blueprint->string('link_label10', 255)->nullable();
            $blueprint->string('link_href10', 255)->nullable();
            $blueprint->string('link_label11', 255)->nullable();
            $blueprint->string('link_href11', 255)->nullable();
            $blueprint->string('link_label12', 255)->nullable();
            $blueprint->string('link_href12', 255)->nullable();

            $blueprint->string('link_label13', 255)->nullable();
            $blueprint->string('link_href13', 255)->nullable();
            $blueprint->string('link_label14', 255)->nullable();
            $blueprint->string('link_href14', 255)->nullable();
            $blueprint->string('link_label15', 255)->nullable();
            $blueprint->string('link_href15', 255)->nullable();
            $blueprint->string('link_label16', 255)->nullable();
            $blueprint->string('link_href16', 255)->nullable();

            $blueprint->string('facebook_url', 255)->nullable();
            $blueprint->string('twitter_url', 255)->nullable();
            $blueprint->string('dribble_url', 255)->nullable();
            $blueprint->string('linkedin_url', 255)->nullable();
            $blueprint->string('behance_url', 255)->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $smCustomLink = new SmCustomLink();
        $smCustomLink->title1 = 'Departments';
        $smCustomLink->title2 = 'Health Care';
        $smCustomLink->title3 = 'About Our System';
        $smCustomLink->title4 = 'Resources';

        $smCustomLink->link_label1 = 'About Infix';
        $smCustomLink->link_href1 = 'http://infixedu.com';

        $smCustomLink->link_label2 = 'Infix Home';
        $smCustomLink->link_href2 = 'http://infixedu.com/home';

        $smCustomLink->link_label3 = 'Business';
        $smCustomLink->link_href3 = 'http://infixedu.com';

        $smCustomLink->link_label4 = 'link_label4';
        $smCustomLink->link_href4 = 'http://infixedu.com';

        $smCustomLink->link_label5 = 'link_label5';
        $smCustomLink->link_href5 = 'http://infixedu.com';

        $smCustomLink->link_label6 = 'link_label6';
        $smCustomLink->link_href6 = 'http://infixedu.com';

        $smCustomLink->link_label7 = 'link_label7';
        $smCustomLink->link_href7 = 'http://infixedu.com';

        $smCustomLink->link_label8 = 'link_label8';
        $smCustomLink->link_href8 = 'http://infixedu.com';

        $smCustomLink->link_label9 = 'Home';
        $smCustomLink->link_href9 = 'http://infixedu.com/home';

        $smCustomLink->link_label10 = 'About';
        $smCustomLink->link_href10 = 'http://infixedu.com/about';

        $smCustomLink->link_label11 = 'Contact';
        $smCustomLink->link_href11 = 'http://infixedu.com/contact';

        $smCustomLink->link_label12 = 'link_label12';
        $smCustomLink->link_href12 = 'http://infixedu.com';

        $smCustomLink->link_label13 = 'link_label13';
        $smCustomLink->link_href13 = 'http://infixedu.com';

        $smCustomLink->link_label14 = 'link_label14';
        $smCustomLink->link_href14 = 'http://infixedu.com';

        $smCustomLink->link_label15 = 'link_label15';
        $smCustomLink->link_href15 = 'http://infixedu.com';

        $smCustomLink->link_label16 = 'link_label16';
        $smCustomLink->link_href16 = 'http://infixedu.com';

        // $s->facebook_url = 'https://www.facebook.com/SchoolManagementSoftwarePro/';
        // $s->twitter_url  = 'https://twitter.com/infix_official';
        // $s->dribble_url  = 'https://dribbble.com/codethemes';
        // $s->linkedin_url  = 'https://www.linkedin.com/in/infix-edu-875458190/';
        // $s->behance_url  = '';
        $smCustomLink->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_custom_links');
    }
}
