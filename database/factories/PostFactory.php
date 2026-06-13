<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\PostType;
use App\Models\Post;
use App\Traits\GeneralHelpers;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    use GeneralHelpers;

    protected $model = Post::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Post $post) {
            $post->name = $this->generateSlug($post, $post->title, 'name');
        });
    }

    public function definition(): array
    {
        // User ID kezelés
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            $user    = User::factory()->create();
            $userIds = [$user->id];
        }

        // PostType kezelés

        $title = fake()->name() . " - " . fake()->sentence();

        return [
            'title'          => $title,
            'user_id'        => $this->faker->randomElement($userIds),
            'content'        => fake('hu_HU')->realText(500, 5),
            'status'         => $this->faker->randomElement(['published', 'draft', 'trash', 'private']), // Véletlenszerű státusz
            'post_type_name' => 'post',
            'created_at'     => now(),
            'updated_at'     => now()
        ];
    }
}