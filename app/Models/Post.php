<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;


class Post extends Model
{
    use HasFactory;
    use EnsureUniqueTitleAndNameTrait;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'user_id',
        'post_type_name',
        'content',
        'status',
        'created_at',
        'updated_at',
    ];


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
     * Method previousPost
     *
     * @return void
     */
    public function prevPost()
    {
        return self::where('status', 'published')
            ->where('post_type_name', $this->post_type_name)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Method nextPost
     *
     * @return void
     */
    public function nextPost()
    {
        return self::where('status', 'published')
            ->where('post_type_name', $this->post_type_name)
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Get post author user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Method author
     *
     * @return void
     */
    public function author()
    {
        return $this->user();
    }

    // A több-a-többhöz kapcsolat a 'post_terms' táblával a 'post_term_relationships' táblán keresztül
    public function terms()
    {
        return $this->belongsToMany(PostTerm::class, 'post_term_relationships', 'post_id', 'post_term_id');
    }

    /**
     * Get post term relations
     */
    public function termRelations(): hasMany
    {
        return $this->hasMany(PostTermRelationship::class, 'post_id', 'id');
    }

    /**
     * Method taxonomies
     *
     * @return void
     */
    public function taxonomies()
    {
        return $this->terms()->with('taxonomy')->get()->pluck('taxonomy')->unique();
    }

    /**
     * A User "has many" post metas.
     */
    public function metas(): HasMany
    {
        return $this->hasMany(PostMeta::class);
    }

    /**
     * Method scopeWithMeta
     *
     * @param $query $query [explicite description]
     * @param string $metaName [explicite description]
     *
     * @return void
     */
    public function scopeWithMeta($query, string $metaName)
    {
        return $query->with(['metas' => function($q) use ($metaName) {
            $q->where('name', $metaName);
        }]);
    }

    /**
     * Get post image albums
     */
    public function imageAlbums()
    {
        return $this->hasMany(PostImageAlbum::class, 'post_id', 'id');
    }

    /**
     * Method files
     *
     * @return void
     */
    public function files()
    {
        return $this->belongsToMany(
            PostFile::class,            // Kapcsolódó modell
            'post_file_relationships',  // Kapcsolótábla neve
            'post_id',                  // Jelen modell külső kulcsa
            'post_file_id'              // Kapcsolódó modell külső kulcsa
        )->withPivot('order');          // Opcionális: ha kell a pivot mező
    }

    /**
     * Method fileRelations
     *
     * @return hasMany
     */
    public function fileRelations(): hasMany
    {
        return $this->hasMany(PostFileRelationship::class, 'post_id', 'id');
    }

    /**
     * Method images
     *
     * @return void
     */
    public function images()
    {
        return $this->belongsToMany(
            PostImage::class,          // Kapcsolódó modell
            'post_image_relationships', // Kapcsolótábla neve
            'post_id',                  // Jelen modell külső kulcsa
            'post_image_id'              // Kapcsolódó modell külső kulcsa
        )->withPivot('order');          // Opcionális: ha kell a pivot mező
    }

    /**
     * Method imagesInAlbum
     *
     * @param int $albumId [explicite description]
     *
     * @return void
     */
    public function imagesInAlbum($albumId)
    {
        return PostImageAlbum::find($albumId)->images;
    }

    /**
     * Method parent
     *
     * @return void
     */
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    /**
     * Method parents
     *
     * @return void
     */
    public function parents()
    {
        return $this->hasMany(Post::class, 'parent_id');
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
        return $this->hasMany(Post::class, 'parent_id');
    }

    /**
     * Method directDescendants
     *
     * @return void
     */
    public function directDescendants()
    {
        // Lekérjük az összes közvetlen gyermeket
        $children = $this->directChildren;

        // Majd minden gyermekre alkalmazzuk a rekurzív keresést, hogy azok gyerekeit is megtaláljuk
        $allChildren = collect();

        foreach ($children as $child) {
            // Az összes gyermeket és azok gyermekeit hozzáadjuk a kollekcióhoz
            $allChildren->push($child);
            $allChildren = $allChildren->merge($child->directDescendants());
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
