<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

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
        'password' => 'password1',
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
        'password' => 'password',
    ]);

    $response->assertStatus(200);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(true);
});

it('forgot password bad request', function () {
    // $user = User::factory()->create();

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
        'email' => $user->email,
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
        'email' => $user->email,
    ]);

    $response->assertStatus(200);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(true)
        ->message->toBe('Mail send successfully');
});

it('reset password bad request', function () {
    // $user = User::factory()->create();

    $response = $this->post('/api/reset-password', []);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(3)
        ->success->toBe(false);
});

it('reset password bad token', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $new_password = '12345678';

    $response = $this->post('/api/reset-password', [
        'token' => '123',
        'email' => $user->email,
        'password' => $new_password,
        'password_confirmation' => $new_password,
    ]);

    $response->assertStatus(400);

    expect(json_encode($response->json(), true))
        ->json()
        ->toHaveCount(2)
        ->success->toBe(false);
});

it('reset password success', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/api/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $new_password = 'testing1234';

        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => $new_password,
            'password_confirmation' => $new_password,
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
});

// ===== PRUEBAS ADICIONALES PARA COBERTURA COMPLETA =====

it('registration with invalid email format', function () {
    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('registration with password too short', function () {
    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('registration with password confirmation mismatch', function () {
    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('registration with duplicate email', function () {
    $existingUser = User::factory()->create();

    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('registration triggers registered event', function () {
    Event::fake();

    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
});

it('registration creates hashed password', function () {
    $password = 'password123';

    $response = $this->post('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'test@example.com')->first();
    expect(\Illuminate\Support\Facades\Hash::check($password, $user->password))->toBe(true);
});

it('login with invalid email format', function () {
    $response = $this->post('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('login with password too short', function () {
    $response = $this->post('/api/login', [
        'email' => 'test@example.com',
        'password' => '123',
    ]);

    $response->assertStatus(400);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('login with non-existent user', function () {
    $response = $this->post('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(404);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('login returns valid token', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $data = $response->json();

    expect($data['data'])->toHaveKey('token');
    expect($data['data'])->toHaveKey('name');
    expect($data['data']['name'])->toBe($user->name);
});

it('check reset password token with valid token', function () {
    Notification::fake();
    $user = User::factory()->create();

    // Primero enviamos el email de reset
    $this->post('/api/forgot-password', ['email' => $user->email]);

    // Verificamos que se envió la notificación y obtenemos el token
    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
        $response = $this->get('/api/reset-password/'.$notification->token);
        $response->assertStatus(200);

        expect(json_encode($response->json(), true))
            ->json()
            ->success->toBe(true);

        return true;
    });
});

it('check reset password token with invalid token', function () {
    $response = $this->get('/api/reset-password/invalid-token-123');

    $response->assertStatus(404);
    expect(json_encode($response->json(), true))
        ->json()
        ->success->toBe(false);
});

it('password reset triggers password reset event', function () {
    Event::fake();
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $newPassword = 'newpassword123';

        $response = $this->post('/api/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(\Illuminate\Auth\Events\PasswordReset::class);

        return true;
    });
});
