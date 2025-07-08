<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Client;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a personal access client for testing in Laravel 12
        // This is needed for createToken() to work in tests
        Client::create([
            'id' => 1,
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
