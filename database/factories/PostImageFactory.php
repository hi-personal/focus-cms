<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PostImageFactory extends Factory
{
    public function definition(): array
    {
        $faker = \Faker\Factory::create();

        // Egyedi név generálása
        $name = $this->generateUniqueValue(
            fn() => Str::slug($faker->words(2, true)) . '-' . Str::random(5),
            'post_images',
            'name'
        );

        // Egyedi cím generálása
        $title = $this->generateUniqueValue(
            fn() => $faker->sentence(3),
            'post_images',
            'title'
        );

        // Egyedi file_uri generálása
        $fileUri = $this->generateUniqueValue(
            fn() => '/storage/images/' . Str::slug($faker->words(2, true)) . '.jpg',
            'post_images',
            'file_uri'
        );

        // Egyedi file_url generálása
        $fileUrl = $this->generateUniqueValue(
            fn() => url('/storage/images/' . Str::slug($faker->words(2, true)) . '.jpg'),
            'post_images',
            'file_url'
        );

        return [
            'name' => $name,
            'title' => $title,
            'file_uri' => $fileUri,
            'file_url' => $fileUrl,
            'mime_type' => $faker->randomElement(['image/jpeg', 'image/png', 'image/gif']),
            'file_size' => $faker->numberBetween(100000, 10000000)
        ];
    }

    /**
     * Generál egy egyedi értéket a megadott mezőhöz
     */
    private function generateUniqueValue(callable $generator, string $table, string $column): string
    {
        do {
            $value = $generator();
            $exists = DB::table($table)->where($column, $value)->exists();
        } while ($exists);

        return $value;
    }
}