<?php

use Filament\Facades\Filament;
use Filament\Livewire\SimpleUserMenu;
use Mortezamasumi\FbProfile\Pages\EditProfile;
use Mortezamasumi\FbProfile\Tests\Services\User;

beforeEach(function () {
    /** @var Pest $this */
    $this->actingAs($this->user = User::factory()->create());
});

it('can see profile in user menu', function () {
    /** @var Pest $this */
    $this
        ->Livewire(SimpleUserMenu::class)
        ->assertSee('Profile');
});

it('can update profile', function () {
    $data = [
        'email' => fake()->unique()->safeEmail(),
        'username' => fake()->userName(),
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'nid' => fake()->numerify('##########'),
        'birth_date' => fake()->date(),
        'mobile' => fake()->numerify('09#########'),
    ];

    /** @var Pest $this */
    $this
        ->livewire(EditProfile::class)
        ->fillForm($data)
        ->call('save')
        ->assertHasNoFormErrors();

    $this->user->refresh();

    foreach ($data as $key => $field) {
        $this->assertDatabaseHas('users', [$key => $field]);
    }
});

it('can redirect after update', function () {
    $data = [
        'email' => fake()->unique()->safeEmail(),
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
    ];

    /** @var Pest $this */
    $this
        ->livewire(EditProfile::class)
        ->fillForm($data)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getLoginUrl());
});
