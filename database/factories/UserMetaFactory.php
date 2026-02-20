<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserMetaFactory extends Factory
{
    public function definition(): array
    {
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $user = User::factory()->create();
            $userIds = [$user->id];
        }

        return [
            'user_id' => $this->faker->randomElement($userIds),
            'name' => $this->faker->unique()->word . '_meta',
            'value' => $this->faker->sentence(),
            'transient' => $this->faker->boolean(30),
            'valid' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', '+2 years')
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'name' => $this->faker->unique()->word . '_' . $user->id
        ]);
    }
}