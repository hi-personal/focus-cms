<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostImage;
use Illuminate\Support\Facades\DB;

class PostImageMetaFactory extends Factory
{
    private $metaPatterns = [
        'description' => ['type' => 'sentence', 'params' => []],
        'author' => ['type' => 'name', 'params' => []],
        'location' => ['type' => 'city', 'params' => []],
        'camera' => ['type' => 'camera_model', 'params' => []],
        'iso' => ['type' => 'number', 'params' => [100, 25600]],
        'aperture' => ['type' => 'aperture', 'params' => []],
        'exposure' => ['type' => 'exposure', 'params' => []],
        'keywords' => ['type' => 'keywords', 'params' => [3, 7]],
        'license' => ['type' => 'license', 'params' => []]
    ];

    public function definition(): array
    {
        $postImage = $this->getValidPostImage();
        $metaKey = $this->generateUniqueMetaKey($postImage->id);
        $metaValue = $this->generateMetaValue($metaKey);

        return [
            'post_image_id' => $postImage->id,
            'name' => $metaKey,
            'value' => $metaValue
        ];
    }

    private function getValidPostImage()
    {
        return PostImage::inRandomOrder()->first()
            ?? PostImage::factory()->create();
    }

    private function generateUniqueMetaKey(int $postImageId): string
    {
        $attempt = 0;
        $maxAttempts = 50;

        do {
            $key = array_rand($this->metaPatterns);
            $exists = DB::table('post_image_metas')
                ->where('post_image_id', $postImageId)
                ->where('name', $key)
                ->exists();

            $attempt++;

            if ($attempt > $maxAttempts) {
                return 'custom_' . Str::random(8);
            }

        } while ($exists);

        return $key;
    }

    private function generateMetaValue(string $key): string
    {
        $pattern = $this->metaPatterns[$key]
            ?? $this->metaPatterns['description'];

        return match($pattern['type']) {
            'sentence' => $this->faker->sentence,
            'name' => $this->faker->name,
            'city' => $this->faker->city . ', ' . $this->faker->country,
            'camera_model' => $this->faker->randomElement([
                'Canon EOS R5',
                'Nikon Z9',
                'Sony A7 IV',
                'Fujifilm X-T4'
            ]),
            'number' => (string)$this->faker->numberBetween(...$pattern['params']),
            'aperture' => 'f/' . $this->faker->randomElement([1.4, 2.0, 2.8, 4.0, 5.6, 8.0]),
            'exposure' => '1/' . $this->faker->numberBetween(1000, 8000),
            'keywords' => implode(', ', $this->faker->words(
                $this->faker->numberBetween(...$pattern['params'])
            )),
            'license' => $this->faker->randomElement([
                'CC BY 4.0',
                'CC BY-SA 4.0',
                'All Rights Reserved',
                'Public Domain'
            ]),
            default => $this->faker->sentence
        };
    }
}