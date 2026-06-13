<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Facades\DB;

class PostImageRelationshipFactory extends Factory
{
    public function definition(): array
    {
        // Létező vagy új poszt és kép generálása
        $post = Post::inRandomOrder()->first() ?? Post::factory()->create();
        $image = PostImage::inRandomOrder()->first() ?? PostImage::factory()->create();

        // Egyedi páros ellenőrzése
        $this->ensureUniqueCombination($post->id, $image->id);

        return [
            'post_id' => $post->id,
            'post_image_id' => $image->id,
            'order' => $this->faker->numberBetween(0, 100)
        ];
    }

    /**
     * Biztosítja, hogy a kapcsolat egyedi legyen
     */
    private function ensureUniqueCombination(int &$postId, int &$imageId): void
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $exists = DB::table('post_image_relationships')
                ->where('post_id', $postId)
                ->where('post_image_id', $imageId)
                ->exists();

            if (!$exists) return;

            // Új kombináció generálása
            $postId = Post::inRandomOrder()->first()->id ?? Post::factory()->create()->id;
            $imageId = PostImage::inRandomOrder()->first()->id ?? PostImage::factory()->create()->id;

            $attempt++;
        }

        throw new \RuntimeException('Nem sikerült egyedi kapcsolatot generálni 100 próbálkozásból');
    }
}