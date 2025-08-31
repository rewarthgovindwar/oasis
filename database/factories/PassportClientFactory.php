<?php

/** @var Illuminate\Database\Eloquent\Factory $factory */

use Illuminate\Support\Str;
use Laravel\Passport\Client;

$factory->define(Client::class, function (): array {
    return [
        'user_id' => null,
        'name' => fake()->company,
        'secret' => Str::random(40),
        'redirect' => fake()->url,
        'personal_access_client' => false,
        'password_client' => false,
        'revoked' => false,
    ];
});

$factory->state(Client::class, 'password_client', function (): array {
    return [
        'personal_access_client' => false,
        'password_client' => true,
    ];
});
