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
            'state' => fake()->randomElement(['Maharashtra', 'Karnataka', 'Delhi', 'Tamil Nadu', 'Uttar Pradesh', 'Rajasthan', 'Gujarat', 'West Bengal']),
            'city' => fake()->city(),
            'religion' => fake()->randomElement(['Hindu', 'Muslim', 'Christian', 'Sikh', 'Buddhist', 'Jain']),
            'mother_tongue' => fake()->randomElement(['Hindi', 'Tamil', 'Telugu', 'Bengali', 'Marathi', 'Kannada', 'Gujarati']),
            'dietary_preference' => fake()->randomElement(['Vegetarian', 'Non-Vegetarian', 'Eggetarian', 'Vegan']),
            'education' => fake()->randomElement(["Bachelor's", "Master's", 'PhD', 'Diploma', 'High School']),
            'profession' => fake()->randomElement(['Engineer', 'Doctor', 'Teacher', 'Business', 'Designer', 'Student']),
            'income_range' => fake()->randomElement(['₹0-5L', '₹5-10L', '₹10-20L', '₹20L+']),
            'remaining_swipes' => 10,
            'remaining_super_likes' => 5,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
