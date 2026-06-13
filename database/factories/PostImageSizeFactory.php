<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PostImageSizeFactory extends Factory
{
    private $sizePresets = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 1024, 'height' => 768],
        'hd' => ['width' => 1920, 'height' => 1080],
        '4k' => ['width' => 3840, 'height' => 2160]
    ];

    public function definition(): array
    {
        $postImage = PostImage::inRandomOrder()->first()
            ?? PostImage::factory()->create();

        $sizeType = array_rand($this->sizePresets);
        $size = $this->sizePresets[$sizeType];

        return [
            'post_image_id' => $postImage->id,
            'name' => $this->generateUniqueSizeName($postImage->id, $sizeType),
            'file_uri' => $this->generateUniqueFileUri($postImage->file_uri, $sizeType),
            'file_url' => $this->generateUniqueFileUrl($postImage->file_url, $sizeType),
            'mime_type' => $postImage->mime_type ?? 'image/jpeg',
            'file_size' => $this->calculateFileSize($size['width'], $size['height'])
        ];
    }

    private function generateUniqueSizeName(int $postImageId, string $baseName): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $name = "{$baseName}-" . Str::random(4);
            $exists = DB::table('post_image_sizes')
                ->where('post_image_id', $postImageId)
                ->where('name', $name)
                ->exists();

            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        return $exists ? "{$baseName}-" . Str::uuid() : $name;
    }

    private function generateUniqueFileUri(string $originalUri, string $sizeType): string
    {
        $pathInfo = pathinfo($originalUri);
        $newUri = "{$pathInfo['dirname']}/{$pathInfo['filename']}-{$sizeType}.{$pathInfo['extension']}";

        return $this->ensureUniqueValue(
            $newUri,
            'post_image_sizes',
            'file_uri',
            fn() => "{$pathInfo['dirname']}/{$pathInfo['filename']}-{$sizeType}-" . Str::random(4) . ".{$pathInfo['extension']}"
        );
    }

    private function generateUniqueFileUrl(string $originalUrl, string $sizeType): string
    {
        $pathInfo = pathinfo($originalUrl);
        $newUrl = "{$pathInfo['dirname']}/{$pathInfo['filename']}-{$sizeType}.{$pathInfo['extension']}";

        return $this->ensureUniqueValue(
            $newUrl,
            'post_image_sizes',
            'file_url',
            fn() => "{$pathInfo['dirname']}/{$pathInfo['filename']}-{$sizeType}-" . Str::random(4) . ".{$pathInfo['extension']}"
        );
    }

    private function calculateFileSize(int $width, int $height): int
    {
        // Egyszerű becslés: 0.3 byte/pixel (JPEG minőség 80-as)
        return (int) ($width * $height * 0.3);
    }

    private function ensureUniqueValue(
        string $value,
        string $table,
        string $column,
        callable $generator
    ): string {
        $maxAttempts = 5;
        $attempt = 0;

        while (DB::table($table)->where($column, $value)->exists() && $attempt < $maxAttempts) {
            $value = $generator();
            $attempt++;
        }

        return $attempt < $maxAttempts
            ? $value
            : $generator() . '-' . Str::uuid();
    }
}