<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVersionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('version_histories', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('version')->nullable();
            $blueprint->string('release_date')->nullable();
            $blueprint->string('url')->nullable();
            $blueprint->string('notes')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_histories');
    }
}
