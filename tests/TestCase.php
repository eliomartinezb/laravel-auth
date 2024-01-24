<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $clientRepository = new ClientRepository();
        $clientRepository->createPersonalAccessClient(
            null, 'Personal Access Client', 'http://localhost'
        );

        Artisan::call('passport:install');
    }
}
