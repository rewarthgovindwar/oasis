<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_access_tokens', function (Blueprint $blueprint): void {
            $blueprint->string('id');
            $blueprint->bigInteger('user_id')->index()->nullable();
            $blueprint->unsignedInteger('client_id');
            $blueprint->string('name', 100)->nullable();
            $blueprint->string('scopes', 100)->nullable();
            $blueprint->string('revoked', 100);
            $blueprint->dateTime('expires_at')->nullable();
            $blueprint->timestamps();
        });
    }

    /**`
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
}
