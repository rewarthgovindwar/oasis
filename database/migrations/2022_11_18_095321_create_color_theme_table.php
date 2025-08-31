<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateColorThemeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('color_theme', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('color_id')->nullable();
            $blueprint->string('value')->nullable();
            $blueprint->foreign('color_id')->on('colors')->references('id')->onDelete('cascade');
            $blueprint->foreignId('theme_id')->nullable();
            $blueprint->foreign('theme_id')->on('themes')->references('id')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $themes = App\Models\Theme::withOutGlobalScopes()->get();
        $sql = [];
        foreach ($themes as $theme) {
            if ($theme->title === 'Default') {
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 1, 'value' => '#415094'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 2, 'value' => '#7c32ff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 3, 'value' => '#7c32ff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 4, 'value' => '#7c32ff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 5, 'value' => '#828bb2'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 6, 'value' => '#828bb2'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 7, 'value' => '#ffffff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 8, 'value' => '#ffffff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 9, 'value' => '#000000'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 10, 'value' => '#000000'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 11, 'value' => '#EFF2F8'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 12, 'value' => '#ffffff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 13, 'value' => '#51A351'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 14, 'value' => '#E09079'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 15, 'value' => '#FF6D68'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 16, 'value' => '#415094'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 17, 'value' => '#222222'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 18, 'value' => '#415094'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 19, 'value' => '#0d0e12'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 20, 'value' => '#ffffff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 21, 'value' => '#ffffff'];
                $sql[] = ['theme_id' => $theme->id, 'color_id' => 29, 'value' => '#415094'];

            }
        }

        DB::table('color_theme')->insert($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('color_theme', function (Blueprint $blueprint): void {
            $blueprint->dropForeign('color_theme_color_id_foreign');
            $blueprint->dropForeign('color_theme_theme_id_foreign');
        });
        Schema::dropIfExists('color_theme');
    }
}
