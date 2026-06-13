<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PostFileFactory extends Factory
{
    private $mimeToExtension = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/plain' => 'txt',
        'application/zip' => 'zip'
    ];

    public function definition(): array
    {
        $name = $this->generateUniqueValue('name');
        $extension = $this->getExtensionForMime();

        return [
            'name' => $name,
            'title' => $this->generateUniqueValue('title', fn() => $this->faker->sentence(3)),
            'file_uri' => $this->generateFileUri($name, $extension),
            'file_url' => $this->generateFileUrl($name, $extension),
            'mime_type' => array_rand($this->mimeToExtension),
            'file_size' => $this->faker->numberBetween(1024, 10485760) // 1KB - 10MB
        ];
    }

    private function generateUniqueValue(
        string $column,
        ?callable $generator = null
    ): string {
        $maxAttempts = 10;
        $attempt = 0;
        $generator = $generator ?? fn() => Str::slug($this->faker->words(2, true));

        do {
            $value = $generator();
            $exists = DB::table('post_files')->where($column, $value)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        return $exists ? $value . '-' . Str::random(4) : $value;
    }

    private function getExtensionForMime(): string
    {
        $mime = array_rand($this->mimeToExtension);
        return $this->mimeToExtension[$mime];
    }

    private function generateFileUri(string $name, string $extension): string
    {
        return $this->generateUniqueValue('file_uri', function() use ($name, $extension) {
            return "/storage/files/{$name}-" . Str::random(4) . ".{$extension}";
        });
    }

    private function generateFileUrl(string $name, string $extension): string
    {
        return $this->generateUniqueValue('file_url', function() use ($name, $extension) {
            return url("/storage/files/{$name}-" . Str::random(4) . ".{$extension}");
        });
    }

    public function configure()
    {
        return $this->afterCreating(function ($file) {
            $this->updateFilePaths($file);
        });
    }

    private function updateFilePaths($file)
    {
        $extension = $this->mimeToExtension[$file->mime_type];
        $file->update([
            'file_uri' => "/storage/files/{$file->name}.{$extension}",
            'file_url' => url("/storage/files/{$file->name}.{$extension}")
        ]);
    }
}