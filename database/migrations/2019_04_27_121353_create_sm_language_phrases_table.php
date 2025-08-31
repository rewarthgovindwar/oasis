<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLanguagePhrasesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_language_phrases', function (Blueprint $blueprint): void {
            $blueprint->collation = 'utf8_general_ci';
            $blueprint->charset = 'utf8';
            $blueprint->increments('id');
            $blueprint->text('modules')->nullable();
            $blueprint->text('default_phrases')->nullable();
            $blueprint->text('en')->nullable();
            $blueprint->text('es')->nullable();
            $blueprint->text('bn')->nullable();
            $blueprint->text('fr')->nullable();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->tinyInteger('active_status')->default('1');
            $blueprint->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_language_phrases');
    }
}
