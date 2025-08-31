<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthClientsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_clients', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->bigInteger('user_id')->index()->nullable();
            $blueprint->string('provider')->nullable();
            $blueprint->string('name', 191);
            $blueprint->string('secret', 200);
            $blueprint->text('redirect');
            $blueprint->boolean('personal_access_client');
            $blueprint->boolean('password_client');
            $blueprint->boolean('revoked');
            $blueprint->timestamps();
        });

        $redirect_url = url('/');

        $oauth = new App\Models\OauthClient;
        $oauth->provider = null;
        $oauth->name = 'Laravel Personal Access Client';
        $oauth->secret = '2e1LEl0zBTmD8XN4sa0meCTtKslUBpShKW4AGrej';
        $oauth->redirect = $redirect_url;
        $oauth->personal_access_client = 1;
        $oauth->password_client = 0;
        $oauth->revoked = 0;
        $oauth->saveQuietly();

        $oauth = new App\Models\OauthClient;
        $oauth->provider = 'users';
        $oauth->name = 'Laravel Password Grant Client';
        $oauth->secret = 'oDaHAi0ml3To8OC7Da10TGVUm7zjhMyq00cmwoDZ';
        $oauth->redirect = $redirect_url;
        $oauth->personal_access_client = 0;
        $oauth->password_client = 1;
        $oauth->revoked = 0;
        $oauth->saveQuietly();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
}
