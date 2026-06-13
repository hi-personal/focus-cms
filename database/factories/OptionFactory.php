<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Option>
 */
class OptionFactory extends Factory
{
    protected $model = Option::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Egyedi name generálása
        do {
            $name = $this->faker->unique()->word;
            $exists = Option::where('name', $name)->exists();
        } while ($exists);

        return [
            'name' => $name,
            'value' => $this->faker->optional()->paragraph,
            'transient' => $this->faker->boolean,
            'valid' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
