<?php

use App\Models\MaintenanceSetting;
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
        Schema::create('maintenance_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title')->default('We will be back soon!')->nullable();
            $blueprint->string('sub_title')->default('Sorry for the inconvenience but we are performing some maintenance at the moment.')->nullable();
            $blueprint->string('image')->nullable();
            $blueprint->string('applicable_for')->nullable();
            $blueprint->boolean('maintenance_mode')->nullable()->default(0);
            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $maintenanceSetting = new MaintenanceSetting();
        $maintenanceSetting->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_settings');
    }
};
