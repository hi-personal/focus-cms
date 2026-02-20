<?php

namespace Database\Factories;

use App\Models\PostMeta;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostMeta>
 */
class PostMetaFactory extends Factory
{
    protected $model    = PostMeta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Meglévő post ID-k lekérése
        $postIds        = Post::pluck('id')->toArray();

        return [
            'post_id'   =>  $this->faker->randomElement($postIds),
            'name'      =>  $this->faker->unique()->word,
            'value'     =>  $this->faker->optional()->paragraph,
        ];
    }
}
