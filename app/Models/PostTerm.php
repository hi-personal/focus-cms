<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;
use App\Services\TaxonomyRegistry;

class PostTerm extends Model
{
    use HasFactory;
    use EnsureUniqueTitleAndNameTrait;

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'post_taxonomy_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function metas(): HasMany
    {
        return $this->hasMany(
            PostTermMeta::class
        );
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'post_term_relationships',
            'post_term_id',
            'post_id'
        )
        ->orderBy(
            'created_at',
            'desc'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            PostTerm::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            PostTerm::class,
            'parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tree helpers
    |--------------------------------------------------------------------------
    */

    public function directChildren(): HasMany
    {
        return $this->children();
    }

    public function getAllParents()
    {
        $parents=collect();

        $current=$this;

        while($current->parent_id){

            $current=$current->parent;

            $parents->push(
                $current
            );

        }

        return $parents;
    }

    public function directDescendants(
        $depth = 0
    )
    {
        $depth++;

        $children=$this->directChildren;

        $allChildren=collect();

        foreach($children as $child){

            $child->depth=$depth;

            $allChildren->push(
                $child
            );

            $allChildren=$allChildren->merge(
                $child->directDescendants(
                    $depth
                )
            );

        }

        return $allChildren;
    }

    public function allDescendants()
    {
        return $this->directDescendants();
    }

    /*
    |--------------------------------------------------------------------------
    | Taxonomy helpers
    |--------------------------------------------------------------------------
    */

    public static function findTerm(
        string $taxonomy,
        string $name,
        ?string $locale = null,
        bool $default = false
    ): ?self {

        $locale ??= app()->getLocale();

        return static::where(
            'post_taxonomy_name',
            $taxonomy
        )
        ->get()
        ->first(function($term) use (
            $name,
            $locale,
            $default
        ){

            return $term->localizedName(
                $locale,
                $default
            ) === $name;

        });

    }

    public function taxonomy(): array
    {
        return TaxonomyRegistry::get(
            $this->post_taxonomy_name
        );
    }

    public function isHierarchical(): bool
    {
        return TaxonomyRegistry::isHierarchical(
            $this->post_taxonomy_name
        );
    }

    public function taxonomyTitle(): string
    {
        return $this->taxonomy()['title']
            ?? $this->post_taxonomy_name;
    }

    /*
    |--------------------------------------------------------------------------
    | Locale helpers
    |--------------------------------------------------------------------------
    */

    public function localizedTitle(
        ?string $locale = null,
        bool $default = true
    ): ?string {

        return $this->getMetaValue(
            'title',
            $locale,
            $default
        );
    }

    public function localizedName(
        ?string $locale = null,
        bool $default = true
    ): ?string {

        return $this->getMetaValue(
            'name',
            $locale,
            $default
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Meta helpers
    |--------------------------------------------------------------------------
    */

    public function meta(
        string $key,
        ?string $locale = null,
        bool $default = true
    ): ?string {

        return PostTermMeta::getValue(
            $this->id,
            $key,
            $locale,
            $default
        );
    }

    public function getMetaValue(
        string $key,
        ?string $locale = null,
        bool $default = true
    ): ?string {

        return $this->meta(
            $key,
            $locale,
            $default
        );
    }

    public function setMetaValue(
        string $key,
        ?string $value,
        ?string $locale = null
    ): void {

        PostTermMeta::setValue(
            $this->id,
            $key,
            $value,
            $locale
        );
    }

    public function metaCollection(
        ?string $locale = null
    )
    {
        return PostTermMeta::getAll(
            $this->id,
            $locale
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Title / Name setters
    |--------------------------------------------------------------------------
    */

    public function setTitle(
        ?string $value,
        ?string $locale = null
    ): void {

        $this->setMetaValue(
            'title',
            $value,
            $locale
        );
    }

    public function setName(
        ?string $value,
        ?string $locale = null
    ): void {

        $this->setMetaValue(
            'name',
            $value,
            $locale
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation helpers
    |--------------------------------------------------------------------------
    */

    public static function prevPostInTerm(
        Post $post,
        PostTerm $term
    ){
        return $term->posts()
            ->where(
                'status',
                'published'
            )
            ->where(
                'post_type_name',
                $post->post_type_name
            )
            ->where(
                'created_at',
                '<',
                $post->created_at
            )
            ->where(
                'id',
                '!=',
                $post->id
            )
            ->orderBy(
                'created_at',
                'desc'
            )
            ->first();
    }

    public static function nextPostInTerm(
        Post $post,
        PostTerm $term
    ){
        return $term->posts()
            ->where(
                'status',
                'published'
            )
            ->where(
                'post_type_name',
                $post->post_type_name
            )
            ->where(
                'created_at',
                '>',
                $post->created_at
            )
            ->where(
                'id',
                '!=',
                $post->id
            )
            ->orderBy(
                'created_at',
                'asc'
            )
            ->first();
    }
}