<?php

use App\Enums\ClientType;
use App\Enums\UserRole;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('new users registration auto-creates a retail account', function () {
    $this->post(route('register.store'), [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'jane@example.com')->firstOrFail();
    expect($user->role)->toBe(UserRole::Client);

    $account = $user->account;
    expect($account)->not->toBeNull();
    expect($account->client_type)->toBe(ClientType::Retail);
    expect($account->account_number)->toStartWith('BM-');
});