<?php

use App\SmSystemVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmSystemVersionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_system_versions', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('version_name', 255);
            $blueprint->string('title', 255);
            $blueprint->string('features', 255);
            $blueprint->timestamps();
        });

        $smSystemVersion = new SmSystemVersion();
        $smSystemVersion->version_name = '3.2';
        $smSystemVersion->title = 'Upgrade System Integration';
        $smSystemVersion->features = 'features 1, features 2';
        $smSystemVersion->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_system_versions');
    }
}
