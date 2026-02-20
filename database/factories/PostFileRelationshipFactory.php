<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\PostFile;

class PostFileRelationshipFactory extends Factory
{
    /**
     * Biztonságos kapcsolatgenerálás tömeges adatokhoz
     */
    public function definition(): array
    {
        // 1. Létező posztok betöltése vagy új generálása
        $postIds = $this->getValidPostIds();

        // 2. Létező fájlok betöltése vagy új generálása
        $fileIds = $this->getValidFileIds();

        // 3. Egyedi páros keresése
        [$postId, $fileId] = $this->findUniquePair($postIds, $fileIds);

        return [
            'post_id' => $postId,
            'post_file_id' => $fileId,
            'order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Létező post ID-k lekérése
     */
    private function getValidPostIds(): array
    {
        $postIds = Post::pluck('id')->toArray();

        if (empty($postIds)) {
            $post = Post::factory()->create();
            $postIds = [$post->id];
        }

        return $postIds;
    }

    /**
     * Létező fájl ID-k lekérése
     */
    private function getValidFileIds(): array
    {
        $fileIds = PostFile::pluck('id')->toArray();

        if (empty($fileIds)) {
            $file = PostFile::factory()->create();
            $fileIds = [$file->id];
        }

        return $fileIds;
    }

    /**
     * Egyedi (post_id + file_id) páros keresése
     */
    private function findUniquePair(array $postIds, array $fileIds): array
    {
        $maxAttempts = count($postIds) * count($fileIds) * 2;
        $attempt = 0;

        do {
            $postId = $this->faker->randomElement($postIds);
            $fileId = $this->faker->randomElement($fileIds);

            $exists = DB::table('post_file_relationships')
                ->where('post_id', $postId)
                ->where('post_file_id', $fileId)
                ->exists();

            $attempt++;

            if ($attempt > $maxAttempts) {
                // Új fájl generálása ha túl sok próbálkozás
                $newFile = PostFile::factory()->create();
                $fileId = $newFile->id;
                $exists = false;
            }
        } while ($exists);

        return [$postId, $fileId];
    }

    /**
     * Több fájl hozzárendelése egy poszthoz
     */
    public function forPost(Post $post): static
    {
        return $this->state(function (array $attributes) use ($post) {
            $fileIds = PostFile::pluck('id')->toArray();

            if (empty($fileIds)) {
                $file = PostFile::factory()->create();
                $fileIds = [$file->id];
            }

            do {
                $fileId = $this->faker->randomElement($fileIds);
                $exists = DB::table('post_file_relationships')
                    ->where('post_id', $post->id)
                    ->where('post_file_id', $fileId)
                    ->exists();
            } while ($exists);

            return [
                'post_id' => $post->id,
                'post_file_id' => $fileId
            ];
        });
    }
}