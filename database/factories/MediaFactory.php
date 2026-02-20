<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Egyedi file_uri és file_url generálása
        do {
            $fileUri = $this->faker->unique()->filePath();
            $existsFileUri = Media::where('file_uri', $fileUri)->exists();
        } while ($existsFileUri);

        do {
            $fileUrl = $this->faker->unique()->url();
            $existsFileUrl = Media::where('file_url', $fileUrl)->exists();
        } while ($existsFileUrl);

        return [
            'file_uri' => $fileUri,
            'file_url' => $fileUrl,
            'mime_type' => $this->faker->optional()->mimeType,
            'file_size' => $this->faker->numberBetween(1024, 10485760), // Méret: 1 KB és 10 MB között
        ];
    }
}
