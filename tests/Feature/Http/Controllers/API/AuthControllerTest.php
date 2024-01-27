<?php

use App\Models\User;
use function Pest\Faker\fake;

it('registration bad request', function () {
    $response = $this->post('/api/register', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});


it(/**
 * @throws JsonException
 */ 'registration success', function () {
    $user = User::factory()->make();

    $body = [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->post('/api/register', $body);

    $response->assertStatus(201);

    expect(json_encode($response->json()))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(true);
});

it('login bad request credentials', function () {
    $response = $this->post('/api/login', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});

it('login incorrect credentials', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password1'
    ]);

    $response->assertStatus(404);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});

it('login correct', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertStatus(200);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(true);
});

it('forgot password bad request', function () {
    //$user = User::factory()->create();

    $response = $this->post('/api/forgot-password', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});

it('forgot password email doesnt exist', function () {
    $user = User::factory()->make();

    $response = $this->post('/api/forgot-password', [
        'email' => $user->email
    ]);

    $response->assertStatus(404);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(2)
        ->success->toBe(false)
        ->message->toBe('Mail is not in our records');
});

it('forgot password success', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/forgot-password', [
        'email' => $user->email
    ]);

    $response->assertStatus(200);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(true)
        ->message->toBe('Mail send successfully');
});

it('reset password bad request', function () {
    //$user = User::factory()->create();

    $response = $this->post('/api/reset-password', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});

it('reset password bad token', function () {
    //$user = User::factory()->create();

    $response = $this->post('/api/reset-password', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
})->skip();

it('reset password success', function () {
    //$user = User::factory()->create();

    $response = $this->post('/api/reset-password', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
})->skip();
