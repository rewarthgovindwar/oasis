<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmCoursePagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_course_pages', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->timestamps();
            $blueprint->string('title')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->string('main_title')->nullable();
            $blueprint->text('main_description')->nullable();
            $blueprint->string('image')->nullable();
            $blueprint->string('main_image')->nullable();
            $blueprint->string('button_text')->nullable();
            $blueprint->string('button_url')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->boolean('is_parent')->default(1);

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });
        DB::table('sm_course_pages')->insert([
            [
                'title' => 'Course Infix',
                'description' => 'Lisus consequat sapien metus dis urna, facilisi. Nonummy rutrum eu lacinia platea a, ipsum parturient, orci tristique. Nisi diam natoque.',
                'image' => 'public/uploads/about_page/about.jpg',
                'button_text' => 'Learn More News ',
                'button_url' => 'news',
                'main_title' => 'Under Graduate Education',
                'main_description' => 'INFIX has all in one place. You’ll find everything what you are looking into education management system software. We care! User will never bothered in our real eye catchy user friendly UI & UX  Interface design. You know! Smart Idea always comes to well planners. And Our INFIX is Smart for its Well Documentation. Explore in new support world! It’s now faster & quicker. You’ll find us on Support Ticket, Email, Skype, WhatsApp.',
                'main_image' => 'public/uploads/about_page/about-img.jpg',
            ],
        ]);
        DB::table('sm_course_pages')->insert([
            [
                'title' => 'Course Infix',
                'description' => 'Lisus consequat sapien metus dis urna, facilisi. Nonummy rutrum eu lacinia platea a, ipsum parturient, orci tristique. Nisi diam natoque.',
                'image' => 'public/uploads/about_page/about.jpg',
                'button_text' => 'Learn More News ',
                'button_url' => 'news',
                'is_parent' => 0,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_course_pages');
    }
}
