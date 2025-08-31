<?php

use App\Models\Plugin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->boolean('is_enable')->default(false);
            $blueprint->string('availability')->default('both');
            $blueprint->boolean('show_admin_panel')->default(false);
            $blueprint->boolean('show_website')->default(true);
            $blueprint->string('showing_page')->default('all');
            $blueprint->string('applicable_for')->nullable();
            $blueprint->string('position')->nullable();
            $blueprint->string('short_code', 50)->nullable();
            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $defaults = ['tawk', 'messenger'];
        foreach ($defaults as $default) {
            $plugin = new Plugin();
            $plugin->name = $default;
            $plugin->school_id = 1;
            $plugin->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
