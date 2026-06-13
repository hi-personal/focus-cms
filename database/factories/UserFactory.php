<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generálunk egyedi login-t
        $login = $this->generateUniqueLogin();
        $name = $this->generateUniqueName(); // A name is unique, as required

        // Ellenőrizzük, hogy az email egyedi legyen az adatbázisban
        $email = $this->generateUniqueEmail();

        // Az 'status' és 'role' alapértelmezett értékei
        $status = $this->faker->randomElement(['active', 'inactive', 'disabled']);
        $role = $this->faker->randomElement(['admin', 'editor', 'reader']);

        return [
            'name'              => $name,
            'login'             => $login,
            'nicename'          => $login,  // Ugyanaz a login érték
            'display_name'      => $name,   // Az 'display_name' az 'name' alapján
            'email'             => $email,
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'status'            => $status, // 'status' mező
            'role'              => $role,   // 'role' mező
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Generates a unique login.
     *
     * @return string
     */
    protected function generateUniqueLogin(): string
    {
        do {
            $login = $this->faker->lexify('login-????????');
        } while (User::where('login', $login)->exists());

        return $login;
    }

    /**
     * Generates a unique name.
     *
     * @return string
     */
    protected function generateUniqueName(): string
    {
        do {
            $name = $this->faker->name();
        } while (User::where('name', $name)->exists());

        return $name;
    }

    /**
     * Generates a unique email.
     *
     * @return string
     */
    protected function generateUniqueEmail(): string
    {
        do {
            $email = $this->faker->safeEmail();
        } while (User::where('email', $email)->exists());

        return $email;
    }
}