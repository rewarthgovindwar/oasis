<?php

use App\Models\Theme;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title')->nullable();
            $blueprint->string('path_main_style', 255)->nullable();
            $blueprint->string('path_infix_style', 255)->nullable();
            $blueprint->string('replicate_theme', 255)->nullable();
            $blueprint->string('color_mode')->default('gradient');
            $blueprint->boolean('box_shadow')->nullable()->default(true);
            $blueprint->string('background_type')->default('image');
            $blueprint->string('background_color')->nullable();
            $blueprint->string('background_image')->nullable();
            $blueprint->boolean('is_default')->default(false);
            $blueprint->boolean('is_system')->default(false);
            $blueprint->integer('created_by')->nullable();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $schools = SmSchool::all();
        $default_themes = ['Default'];
        foreach ($schools as $school) {
            foreach ($default_themes as $key => $item) {
                $theme = Theme::updateOrCreate([
                    'title' => $item,
                    'school_id' => $school->id,
                ]);
                $theme->path_main_style = 'style.css';
                $theme->path_infix_style = 'infix.css';
                $theme->is_default = $key === 0 ? 1 : 0;
                $theme->color_mode = 'gradient';
                $theme->background_type = 'color';
                // $theme->background_image = 'public/backEnd/img/body-bg.jpg';
                $theme->background_color = '#FAFAFA';
                $theme->is_system = true;
                $theme->created_by = 1;
                $theme->school_id = $school->id;
                $theme->save();
            }

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
}
