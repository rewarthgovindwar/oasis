<?php

use App\SmLanguage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_languages', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('language_name')->nullable();
            $blueprint->string('native')->nullable();
            $blueprint->string('language_universal')->nullable();
            $blueprint->tinyInteger('active_status')->default(0);
            $blueprint->timestamps();

            $blueprint->integer('lang_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('lang_id')->references('id')->on('languages')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        $store = new SmLanguage();
        $store->language_name = 'English';
        $store->native = 'English';
        $store->language_universal = 'en';
        $store->lang_id = 19;
        $store->active_status = 1;
        $store->created_at = date('Y-m-d h:i:s');
        $store->save();

        $store = new SmLanguage();
        $store->language_name = 'Bengali';
        $store->native = 'বাংলা';
        $store->language_universal = 'bn';
        $store->lang_id = 9;
        $store->created_at = date('Y-m-d h:i:s');
        $store->save();

        $store = new SmLanguage();
        $store->language_name = 'Spanish';
        $store->native = 'Español';
        $store->language_universal = 'es';
        $store->lang_id = 20;
        $store->created_at = date('Y-m-d h:i:s');
        $store->save();

        $store->save();
        $store = new SmLanguage();
        $store->language_name = 'French';
        $store->native = 'Français';
        $store->language_universal = 'fr';
        $store->lang_id = 28;
        $store->created_at = date('Y-m-d h:i:s');
        $store->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_languages');
    }
}
