<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAuthCodesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_auth_codes', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->bigInteger('user_id');
            $blueprint->unsignedInteger('client_id');
            $blueprint->text('scopes')->nullable();
            $blueprint->boolean('revoked');
            $blueprint->dateTime('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_auth_codes');
    }
}
