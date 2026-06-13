<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PostFile;

class PostFileMetaFactory extends Factory
{
    public function definition(): array
    {
        $postFileIds = PostFile::pluck('id')->toArray();

        if (empty($postFileIds)) {
            $postFile = PostFile::factory()->create();
            $postFileIds = [$postFile->id];
        }

        return [
            'post_file_id' => $this->faker->randomElement($postFileIds),
            'name' => $this->faker->unique()->word(),
            'value' => $this->faker->sentence()
        ];
    }
}