<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'bio' => fake()->sentence(),
            'profile_photo' => 'https://i.pravatar.cc/150?u='.fake()->uuid(),
            'is_active' => true,
            'is_verified' => false,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'location' => fake()->city(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
