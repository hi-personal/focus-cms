<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostImageAlbum;
use App\Models\PostImage;
use Illuminate\Support\Facades\DB;

class PostImageAlbumRelationshipFactory extends Factory
{
    private $usedPairs = [];

    public function definition(): array
    {
        return [
            'post_image_album_id' => $this->getValidAlbumId(),
            'post_image_id' => $this->getValidImageId(),
            'order' => $this->generateOrder()
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function ($relation) {
            $this->ensureUniquePair($relation);
        });
    }

    private function getValidAlbumId(): int
    {
        return PostImageAlbum::inRandomOrder()->first()->id
            ?? PostImageAlbum::factory()->create()->id;
    }

    private function getValidImageId(): int
    {
        return PostImage::inRandomOrder()->first()->id
            ?? PostImage::factory()->create()->id;
    }

    private function generateOrder(): int
    {
        return $this->faker->numberBetween(0, 100);
    }

    private function ensureUniquePair($relation): void
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($this->pairExists($relation) && $attempt < $maxAttempts) {
            $relation->post_image_album_id = $this->getValidAlbumId();
            $relation->post_image_id = $this->getValidImageId();
            $attempt++;
        }

        if ($attempt === $maxAttempts) {
            throw new \RuntimeException('Nem található egyedi album-kép kombináció 100 próbálkozásból');
        }

        $this->markPairAsUsed($relation);
    }

    private function pairExists($relation): bool
    {
        return DB::table('post_image_album_relationships')
            ->where('post_image_album_id', $relation->post_image_album_id)
            ->where('post_image_id', $relation->post_image_id)
            ->exists()
            || isset($this->usedPairs[$this->getPairKey($relation)]);
    }

    private function getPairKey($relation): string
    {
        return $relation->post_image_album_id . '-' . $relation->post_image_id;
    }

    private function markPairAsUsed($relation): void
    {
        $this->usedPairs[$this->getPairKey($relation)] = true;
    }
}