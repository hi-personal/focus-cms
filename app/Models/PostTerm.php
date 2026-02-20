<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;


class PostTerm extends Model
{
    use HasFactory;

    /**
     * timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'post_taxonomy_name',
        'title',
        'name',
    ];

    use EnsureUniqueTitleAndNameTrait;

    /**
     * Method boot
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->ensureUniqueTitle();
            $model->ensureUniqueName();
        });
    }

    /**
     * Method metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(PostTermMeta::class);
    }

    /**
     * Get the taxonomy details as an object from the configuration.
     *
     * @return object|null
     */
    public function getTaxonomyAttribute(): ?object
    {
        $taxonomy = config("taxonomies.{$this->post_taxonomy_name}");

        return $taxonomy ? (object) $taxonomy : null;
    }

    /**
     * Method posts
     *
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_term_relationships', 'post_term_id', 'post_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Method previousPostInTerm
     *
     * @param Post $post
     * @param PostTerm $term
     *
     * @return void
     */
    public static function prevPostInTerm(Post $post, PostTerm $term)
    {
        return $term->posts()
            ->where('status', 'published')
            ->where('post_type_name', $post->post_type_name)
            ->where('created_at', '<', $post->created_at)
            ->where('id', '!=', $post->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Method nextPostInTerm
     *
     * @param Post $post
     * @param PostTerm $term
     *
     * @return void
     */
    public static function nextPostInTerm(Post $post, PostTerm $term)
    {
        return $term->posts()
            ->where('status', 'published')
            ->where('post_type_name', $post->post_type_name)
            ->where('created_at', '>', $post->created_at)
            ->where('id', '!=', $post->id)
            ->orderBy('created_at', 'asc')
            ->first();
    }


    /**
     * Method parent
     *
     * @return void
     */
    public function parent()
    {
        return $this->belongsTo(PostTerm::class, 'parent_id');
    }

    /**
     * Method parents
     *
     * @return void
     */
    public function parents()
    {
        return $this->hasMany(PostTerm::class, 'parent_id');
    }

    /**
     * Method getAllParents
     *
     * @return void
     */
    public function getAllParents()
    {
        $parents = collect();
        $current = $this;

        while ($current->parent_id) {
            $current = $current->parent;
            $parents->push($current);
        }

        return $parents;
    }

    /**
     * Method directChildren
     *
     * @return void
     */
    public function directChildren()
    {
        return $this->hasMany(PostTerm::class, 'parent_id');
    }

    /**
     * Method directDescendants
     *
     * @return void
     */
    public function directDescendants($depth = 0)
    {
        $depth++;

        // Lekérjük az összes közvetlen gyermeket
        $children = $this->directChildren;

        // Majd minden gyermekre alkalmazzuk a rekurzív keresést, hogy azok gyerekeit is megtaláljuk
        $allChildren = collect();

        foreach ($children as $child) {
            $child->depth = $depth;
            $allChildren->push($child);
            $allChildren = $allChildren->merge($child->directDescendants($depth));
        }

        return $allChildren;
    }

    /**
     * Method allDescendants
     *
     * @return void
     */
    public function allDescendants()
    {
        // Az összes közvetlen gyermeket kérjük le rekurzívan
        return $this->directDescendants();
    }
}