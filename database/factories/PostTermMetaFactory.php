<?php

namespace Database\Factories;

use App\Models\PostTermMeta;
use App\Models\PostTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostTermMeta>
 */
class PostTermMetaFactory extends Factory
{
    protected $model = PostTermMeta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Véletlenszerű `post_term_id` választása a meglévő rekordokból
        $postTermId = PostTerm::inRandomOrder()->first()->id;

        return [
            'post_term_id' => $postTermId,
            'name' => $this->faker->unique()->word, // Egyedi név
            'value' => $this->faker->optional()->text, // Opcionálisan egy hosszú szöveg
        ];
    }
}
