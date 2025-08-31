<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sm_news_comments', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->text('message');

            $blueprint->integer('news_id')->nullable()->unsigned();
            $blueprint->foreign('news_id')->references('id')->on('sm_news')->onDelete('cascade');

            $blueprint->integer('user_id')->nullable()->unsigned();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('parent_id')->nullable();
            $blueprint->tinyInteger('status')->default(0)->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sm_news_comments');
    }
};
