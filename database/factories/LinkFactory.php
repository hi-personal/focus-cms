<?php

namespace Database\Factories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        // Egyedi URL-generálás manuális ellenőrzéssel
        do {
            $url = $this->faker->url();
        } while (Link::where('url', $url)->exists());

        // További mezők kitöltése
        return [
            'url' => $url,
            'image' => $this->faker->optional()->imageUrl(640, 480, 'business', true),
            'target' => $this->faker->optional()->randomElement(['_self', '_blank', '_parent', '_top']),
            'description' => $this->faker->optional()->sentence(),
            'visible' => $this->faker->optional()->word(),
            'rating' => $this->faker->numberBetween(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
            'rel' => $this->faker->optional()->randomElement(['nofollow', 'noopener', 'external']),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}