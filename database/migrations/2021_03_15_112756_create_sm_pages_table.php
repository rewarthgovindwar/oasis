<?php

use App\SmPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmPagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_pages', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title')->nullable();
            $blueprint->string('sub_title')->unique()->nullable();
            $blueprint->string('slug')->nullable();
            $blueprint->text('header_image')->nullable();
            $blueprint->longText('details')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->tinyInteger('is_dynamic')->default(1);

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->timestamps();
        });

        $store = new SmPage();
        $store->id = 1;
        $store->title = 'Home';
        $store->slug = '/';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 2;
        $store->title = 'About';
        $store->slug = '/about';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 3;
        $store->title = 'Course';
        $store->slug = '/course';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 4;
        $store->title = 'News';
        $store->slug = '/news-page';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 5;
        $store->title = 'Contact';
        $store->slug = '/contact';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 6;
        $store->title = 'Login';
        $store->slug = '/login';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 7;
        $store->title = 'Result';
        $store->slug = '/exam-result';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();

        $store = new SmPage();
        $store->id = 8;
        $store->title = 'Routine';
        $store->slug = '/class-exam-routine';
        $store->active_status = 1;
        $store->is_dynamic = 0;
        $store->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_pages');
    }
}
