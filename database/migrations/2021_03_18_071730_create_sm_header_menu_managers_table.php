<?php

use App\SmHeaderMenuManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmHeaderMenuManagersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_header_menu_managers', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('type');
            $blueprint->unsignedBigInteger('element_id')->nullable();
            $blueprint->string('title')->nullable();
            $blueprint->string('link')->nullable();
            $blueprint->unsignedBigInteger('parent_id')->nullable();
            $blueprint->unsignedInteger('position')->default(0);
            $blueprint->boolean('show')->default(0);
            $blueprint->boolean('is_newtab')->default(0);
            $blueprint->string('theme')->default('default');
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->timestamps();
        });

        // $store = new SmHeaderMenuManager();
        // $store->id = 1;
        // $store->type = 'sPages';
        // $store->element_id = 1;
        // $store->title = 'Home';
        // $store->link = '/';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 2;
        // $store->type = 'sPages';
        // $store->element_id = 2;
        // $store->title = 'About';
        // $store->link = '/about';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 3;
        // $store->type = 'sPages';
        // $store->element_id = 3;
        // $store->title = 'Course';
        // $store->link = '/course';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 4;
        // $store->type = 'sPages';
        // $store->element_id = 4;
        // $store->title = 'News';
        // $store->link = '/news-page';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 5;
        // $store->type = 'sPages';
        // $store->element_id = 5;
        // $store->title = 'Contact';
        // $store->link = '/contact';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 6;
        // $store->type = 'sPages';
        // $store->element_id = 6;
        // $store->title = 'Login';
        // $store->link = '/login';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 7;
        // $store->type = 'sPages';
        // $store->element_id = 7;
        // $store->title = 'Result';
        // $store->link = '/exam-result';
        // $store->save();

        // $store = new SmHeaderMenuManager();
        // $store->id = 8;
        // $store->type = 'sPages';
        // $store->element_id = 8;
        // $store->title = 'Routine';
        // $store->link = '/class-exam-routine';
        // $store->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_header_menu_managers');
    }
}
