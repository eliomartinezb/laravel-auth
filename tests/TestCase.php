<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a personal access client for testing in Laravel 12 with Passport 13
        // This is needed for createToken() to work in tests
        // Passport 13 uses UUIDs instead of integer IDs
        Client::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test Personal Access Client',
            'secret' => 'test-secret',
            'provider' => 'users',
            'redirect' => '',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);
    }
}
