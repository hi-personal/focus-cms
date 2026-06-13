<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


trait GeneralHelpers
{
    /**
     * Generate a unique slug for a model.
     *
     * @param Model $model The model instance for which the slug is generated.
     * @param string $title The title from which the slug is derived.
     * @param string $column The column name to check for uniqueness.
     * @return string The generated unique slug.
     */
    public function generateSlug(
        Model $model,
        string $title,
        string $column = 'slug'): string
    {
        // Create a slug from the title using Laravel's Str::slug method
        $slug = Str::slug($title);

        // Check if the generated slug already exists in the specified column of the model's table
        $checkSlug = $model::query()->where($column, $slug)->first();

        // If the slug already exists, append a random string to make it unique
        if ($checkSlug) {
            // Append a random string to the original title to create a new slug
            $title = sprintf("%s %s", $title, Str::random(mt_rand(5, 10)));

            // Recursively call the function with the updated title to generate a new slug
            return $this->generateSlug($model, $title, $column);
        }

        // If the slug is unique, return it
        return $slug;
    }
}
