<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostTerm;
use App\Models\PostTermRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

class PostTermRelationshipFactory extends Factory
{
    protected $model = PostTermRelationship::class;

    private static ?Collection $availablePosts = null;
    private static ?Collection $availableTerms = null;
    private static array $usedPosts = [];
    private string $taxonomy = 'categories';
    private bool $strictMode = false;

    public function definition(): array
    {
        $this->initializeResources();

        return [
            'post_id' => $this->getUniquePostId(),
            'post_term_id' => $this->getUniqueTermId(),
        ];
    }

    private function initializeResources(): void
    {
        $this->ensurePostsExist();
        $this->ensureTermsExist();
    }

    private function ensurePostsExist(): void
    {
        if (!self::$availablePosts) {
            self::$availablePosts = Post::whereNotIn('id', self::$usedPosts)
                ->when($this->strictMode, fn($q) => $q->doesntHave('terms'))
                ->get();

            if (self::$availablePosts->isEmpty()) {
                $count = $this->strictMode ? 50 : 1;
                self::$availablePosts = Post::factory()->count($count)->create();
            }
        }
    }

    private function ensureTermsExist(): void
    {
        if (!self::$availableTerms) {
            self::$availableTerms = PostTerm::where('post_taxonomy_name', $this->taxonomy)
                ->when($this->strictMode, fn($q) => $q->doesntHave('posts'))
                ->get();

            if (self::$availableTerms->isEmpty()) {
                $count = $this->strictMode ? 50 : 1;
                self::$availableTerms = PostTerm::factory()
                    ->count($count)
                    ->create(['post_taxonomy_name' => $this->taxonomy]);
            }
        }
    }

    private function getUniquePostId(): int
    {
        $post = self::$availablePosts->pop();
        self::$usedPosts[] = $post->id;
        return $post->id;
    }

    private function getUniqueTermId(): int
    {
        return $this->strictMode
            ? self::$availableTerms->pop()->id
            : self::$availableTerms->random()->id;
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PostTermRelationship $relationship) {
            $this->refreshResources();
            $this->guardAgainstDuplicates($relationship);
        });
    }

    private function refreshResources(): void
    {
        if (self::$availablePosts->isEmpty()) {
            self::$availablePosts = null;
            self::$usedPosts = [];
        }

        if (self::$availableTerms->isEmpty() && $this->strictMode) {
            self::$availableTerms = null;
        }
    }

    private function guardAgainstDuplicates(PostTermRelationship $relationship): void
    {
        if (PostTermRelationship::where([
            'post_id' => $relationship->post_id,
            'post_term_id' => $relationship->post_term_id
        ])->count() > 1) {
            throw new \RuntimeException('Duplikált kapcsolat észlelve!');
        }
    }

    public function strictMode(bool $enabled = true): static
    {
        $this->strictMode = $enabled;
        return $this;
    }

    public function forTaxonomy(string $taxonomy): static
    {
        $this->taxonomy = $taxonomy;
        return $this;
    }
}