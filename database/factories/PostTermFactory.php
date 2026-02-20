<?php

namespace Database\Factories;

use App\Models\PostTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostTerm>
 */
class PostTermFactory extends Factory
{
    protected $model = PostTerm::class;

    public function definition()
    {
        // Konfigurációs fájlból lekérjük a taxonómiákat
        $taxonomies = array_keys(config('taxonomies', []));

        // Véletlenszerűen választunk egy taxonómiát
        $postTaxonomyName = $this->faker->randomElement($taxonomies);

        // Egyedi title és name generálása
        $title = $this->faker->unique()->word;
        $name = $this->faker->unique()->slug;

        // Ellenőrizzük, hogy a 'title' és 'name' kombinációja nem létezik
        while (
            PostTerm::where('post_taxonomy_name', $postTaxonomyName)
                ->where('title', $title)
                ->where('name', $name)
                ->exists()
        ) {
            $title = $this->faker->unique()->word;
            $name = $this->faker->unique()->slug;
        }

        // Lekérdezzük a létező parent_id-ket (ha vannak)
        $parentIds = PostTerm::where('post_taxonomy_name', $postTaxonomyName)->pluck('id')->toArray();

        // Véletlenszerűen választunk egy létező parent_id-t vagy null-t (nincs szülő)
        $parentId = $this->faker->randomElement(array_merge([null], $parentIds)) ?? 0;

        return [
            'post_taxonomy_name' => $postTaxonomyName,
            'parent_id' => $parentId,
            'title' => $title,
            'name' => $name,
        ];
    }
}