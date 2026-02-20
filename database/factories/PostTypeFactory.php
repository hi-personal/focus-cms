<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostType;

class PostTypeFactory extends Factory
{
    /**
     * A modellekhez tartozó név.
     *
     * @var string
     */
    protected $model = PostType::class;

    /**
     * A modell alapértelmezett állapotának definiálása.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title'       => ucfirst($title),               // Véletlenszerű cím
            'name'        => $this->generateSlug($title),   // Cím slugosítva
            'hierarchial' => fake()->boolean(),             // Véletlenszerűen true/false
            'description' => fake()->optional()->text(200), // Véletlenszerű szöveg vagy null
        ];
    }

    /**
     * Egyedi slug generálása a megadott szövegből.
     *
     * @param string $text
     * @return string
     */
    private function generateSlug(string $text): string
    {
        return strtolower(str_replace(' ', '-', $text));
    }
}