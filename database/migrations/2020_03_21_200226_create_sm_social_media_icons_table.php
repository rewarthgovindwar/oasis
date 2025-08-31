<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmSocialMediaIconsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_social_media_icons', function (Blueprint $blueprint): void {
            $blueprint->bigIncrements('id');
            $blueprint->string('url')->nullable();
            $blueprint->string('icon')->nullable();
            $blueprint->tinyInteger('status')->default(0)->comment('1 active, 0 inactive');
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

        });

        DB::table('sm_social_media_icons')->insert([
            [
                'url' => 'https://www.facebook.com/Spondonit',
                'icon' => 'fa fa-facebook',
                'status' => 1,
            ],
            [
                'url' => 'https://www.facebook.com/Spondonit',
                'icon' => 'fa fa-twitter',
                'status' => 1,
            ],
            [
                'url' => 'https://www.facebook.com/Spondonit',
                'icon' => 'fa fa-dribbble',
                'status' => 1,
            ],
            [
                'url' => 'https://www.facebook.com/Spondonit',
                'icon' => 'fa fa-linkedin',
                'status' => 1,
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_social_media_icons');
    }
}
