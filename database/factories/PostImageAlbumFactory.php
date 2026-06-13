<?php

namespace Database\Factories;

use App\Models\PostImageAlbum;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostImageAlbum>
 */
class PostImageAlbumFactory extends Factory
{
    protected $model = PostImageAlbum::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Meglévő post ID-k lekérése
        $postIds = Post::pluck('id')->toArray();

        // Egyedi name és title generálása
        do {
            $name = $this->faker->unique()->word;
            $existsName = PostImageAlbum::where('name', $name)->exists();
        } while ($existsName);

        do {
            $title = $this->faker->optional()->sentence() ?? $this->faker->sentence();
            $existsTitle = $title ? PostImageAlbum::where('title', $title)->exists() : false;
        } while ($existsTitle);

        return [
            'post_id'       => $this->faker->randomElement($postIds),
            'name'          => $name,
            'title'         => $title,
            'description'   => $this->faker->optional()->paragraph,
        ];
    }
}
