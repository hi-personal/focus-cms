<?php

namespace App\View\Components\Seo;

use App\Models\Post;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class Meta extends Component
{
    public Post $post;

    public string $title;
    public string $description;
    public string $url;
    public string|null $image;
    public ?string $section;
    public array $tags;
    public string $publishedAt;
    public string $modifiedAt;
    public string $ogType;
    public bool $isHome;
    public int $homePageId;

    public function __construct(
        Post $post,
        ?string $title = null,
        ?string $description = null,
        ?string $content = null,
        ?string $image = null,
        ?string $section = null,
        array $tags = [],
        bool $isHome = false,
        int $homePageId = 0
    ) {
        $this->post = $post;
        $this->ogType = $isHome ? 'website' : ($post->post_type_name == "post" ? 'post' : 'article');
        $this->title = $title ?? $post->title;
        $this->isHome = $isHome;
        $this->homePageId = $homePageId;

        $source = $description
            ?? $content
            ?? $post->content
            ?? '';

        $plainText = trim(
            preg_replace(
                '/\s+/',
                ' ',
                strip_tags($source)
            )
        );

        $this->description = Str::limit($plainText, 155, '…');

        $this->url = url()->current();

        $this->image = $image;

        $this->section = $section;

        $this->tags = $tags;

        $this->publishedAt = $post->created_at?->format('Y-m-d')
            ?? now()->toDateString();

        $this->modifiedAt = $post->updated_at?->format('Y-m-d')
            ?? now()->toDateString();
    }

    public function render()
    {
        return view('components.seo.meta');
    }
}
