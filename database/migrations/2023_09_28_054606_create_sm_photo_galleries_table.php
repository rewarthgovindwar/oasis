<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_photo_galleries', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('parent_id')->nullable();
            $blueprint->string('name')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->string('feature_image')->nullable();
            $blueprint->string('gallery_image')->nullable();
            $blueprint->boolean('is_publish')->default(true);
            $blueprint->integer('position')->default(0);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        DB::table('sm_photo_galleries')->insert([
            [
                'parent_id' => null,
                'name' => 'Pre-Primary',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
            [
                'parent_id' => null,
                'name' => 'Kindergarden',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
            [
                'parent_id' => null,
                'name' => 'Celebration',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
            [
                'parent_id' => null,
                'name' => 'Recreation Centre',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
            [
                'parent_id' => null,
                'name' => 'Facilities',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
            [
                'parent_id' => null,
                'name' => 'Activities',
                'description' => 'Fusce semper, nibh eu sollicitudin imperdiet, dolo',
                'feature_image' => 'public/uploads/theme/edulia/photo_gallery/gallery-1.jpg',
                'gallery_image' => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_photo_galleries');
    }
};
