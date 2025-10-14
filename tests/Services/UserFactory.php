<?php

namespace Mortezamasumi\FbProfile\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'nid' => fake()->numerify('##########'),
            'birth_date' => now(),
            'mobile' => fake()->numerify('09#########'),
            'password' => Hash::make('password'),
        ];
    }
}
