<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;

class Post extends Model
{
    use HasFactory;
    use EnsureUniqueTitleAndNameTrait;

    protected $table='posts';

    protected $fillable=[
        'parent_id',
        'lang',
        'lang_parent_id',
        'name',
        'title',
        'user_id',
        'post_type_name',
        'content',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function($model){

            if(empty($model->lang)){

                $model->lang=config('app.locale');

            }

        });

        static::saving(function($model){

            $model->ensureUniqueTitle();

            $model->ensureUniqueName();

            if($model->lang_parent_id){

                $exists=self::where(
                    'lang_parent_id',
                    $model->lang_parent_id
                )
                ->where(
                    'lang',
                    $model->lang
                )
                ->where(
                    'id',
                    '!=',
                    $model->id
                )
                ->exists();

                if($exists){

                    throw new \Exception(
                        "Translation already exists for language: ".$model->lang
                    );

                }

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public function prevPost()
    {
        return self::where(
            'status',
            'published'
        )
        ->where(
            'post_type_name',
            $this->post_type_name
        )
        ->where(
            'lang',
            $this->lang
        )
        ->where(
            'created_at',
            '<',
            $this->created_at
        )
        ->orderBy(
            'created_at',
            'desc'
        )
        ->first();
    }

    public function nextPost()
    {
        return self::where(
            'status',
            'published'
        )
        ->where(
            'post_type_name',
            $this->post_type_name
        )
        ->where(
            'lang',
            $this->lang
        )
        ->where(
            'created_at',
            '>',
            $this->created_at
        )
        ->orderBy(
            'created_at',
            'asc'
        )
        ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function author()
    {
        return $this->user();
    }

    public function terms()
    {
        return $this->belongsToMany(
            PostTerm::class,
            'post_term_relationships',
            'post_id',
            'post_term_id'
        );
    }

    public function termRelations(): HasMany
    {
        return $this->hasMany(
            PostTermRelationship::class,
            'post_id',
            'id'
        );
    }

    public function taxonomies()
    {
        return $this->terms()
            ->with('taxonomy')
            ->get()
            ->pluck('taxonomy')
            ->unique();
    }

    public function metas(): HasMany
    {
        return $this->hasMany(
            PostMeta::class
        );
    }

    public function scopeWithMeta(
        $query,
        string $metaName
    ){
        return $query->with([
            'metas'=>function($q) use ($metaName){

                $q->where(
                    'name',
                    $metaName
                );

            }
        ]);
    }

    public function imageAlbums()
    {
        return $this->hasMany(
            PostImageAlbum::class,
            'post_id',
            'id'
        );
    }

    public function files()
    {
        return $this->belongsToMany(
            PostFile::class,
            'post_file_relationships',
            'post_id',
            'post_file_id'
        )->withPivot('order');
    }

    public function fileRelations(): HasMany
    {
        return $this->hasMany(
            PostFileRelationship::class,
            'post_id',
            'id'
        );
    }

    public function images()
    {
        return $this->belongsToMany(
            PostImage::class,
            'post_image_relationships',
            'post_id',
            'post_image_id'
        )->withPivot('order');
    }

    public function imagesInAlbum($albumId)
    {
        $album=PostImageAlbum::find(
            $albumId
        );

        return $album
            ? $album->images
            : collect();
    }

    public function parent()
    {
        return $this->belongsTo(
            Post::class,
            'parent_id'
        );
    }

    public function children()
    {
        return $this->hasMany(
            Post::class,
            'parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tree helpers
    |--------------------------------------------------------------------------
    */

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

    public function directChildren()
    {
        return $this->hasMany(
            Post::class,
            'parent_id'
        );
    }

    public function directDescendants()
    {
        $children=$this->directChildren;

        $allChildren=collect();

        foreach($children as $child){

            $allChildren->push(
                $child
            );

            $allChildren=$allChildren->merge(
                $child->directDescendants()
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
    | Meta helpers
    |--------------------------------------------------------------------------
    */

    public function meta(
        string $key,
        ?string $locale=null,
        bool $default=true
    ): ?string {

        return PostMeta::getValue(
            $this->id,
            $key,
            $locale,
            $default
        );

    }

    public function getMetaValue(
        string $key,
        ?string $locale=null,
        bool $default=true
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
        ?string $locale=null
    ): void {

        PostMeta::setValue(
            $this->id,
            $key,
            $value,
            $locale
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Multilingual
    |--------------------------------------------------------------------------
    */

    public function isOriginal(): bool
    {
        return empty(
            $this->lang_parent_id
        );
    }

    public function isTranslation(): bool
    {
        return !empty(
            $this->lang_parent_id
        );
    }

    public function getLangGroupId()
    {
        return $this->lang_parent_id
            ?? $this->id;
    }

    public function langParent(): BelongsTo
    {
        return $this->belongsTo(
            Post::class,
            'lang_parent_id'
        );
    }

    public function original()
    {
        return $this->isTranslation()
            ? $this->langParent
            : $this;
    }

    public function translations(): HasMany
    {
        return $this->hasMany(
            Post::class,
            'lang_parent_id'
        )->where(
            'id',
            '!=',
            $this->id
        );
    }

    public function siblings()
    {
        return self::where(function($q){

                $q->where(
                    'lang_parent_id',
                    $this->getLangGroupId()
                )
                ->orWhere(
                    'id',
                    $this->getLangGroupId()
                );

            })
            ->where(
                'id',
                '!=',
                $this->id
            )
            ->get();
    }

    public function translation(
        string $lang
    ){
        return self::where(function($q){

                $q->where(
                    'lang_parent_id',
                    $this->getLangGroupId()
                )
                ->orWhere(
                    'id',
                    $this->getLangGroupId()
                );

            })
            ->where(
                'lang',
                $lang
            )
            ->first();
    }

    public function hasTranslation(
        string $lang
    ): bool {

        return self::where(function($q){

                $q->where(
                    'lang_parent_id',
                    $this->getLangGroupId()
                )
                ->orWhere(
                    'id',
                    $this->getLangGroupId()
                );

            })
            ->where(
                'lang',
                $lang
            )
            ->exists();
    }

    public function allLanguages()
    {
        return self::where(function($q){

            $q->where(
                'lang_parent_id',
                $this->getLangGroupId()
            )
            ->orWhere(
                'id',
                $this->getLangGroupId()
            );

        })->get();
    }

    public function otherLanguages()
    {
        return $this->allLanguages()
            ->where(
                'lang',
                '!=',
                $this->lang
            )
            ->values();
    }

    public function hasTranslations(): bool
    {
        if(!empty($this->lang_parent_id)){

            return false;

        }

        return $this->allLanguages()
            ->where(
                'lang',
                '!=',
                $this->lang
            )
            ->isNotEmpty();
    }

    public function languageMap()
    {
        return $this->allLanguages()
            ->keyBy('lang');
    }

    public function missingTranslations(
        array $langs
    ){
        $existing=$this->allLanguages()
            ->pluck('lang')
            ->toArray();

        return array_diff(
            $langs,
            $existing
        );
    }

    public function currentLocaleVersion()
    {
        return $this->translation(
            app()->getLocale()
        );
    }

    public function createTranslation(
        string $lang
    ){

        if($this->hasTranslation($lang)){

            return null;

        }

        return self::create([

            'title'=>$this->title,

            'name'=>$this->name.'-'.$lang,

            'user_id'=>auth()->id(),

            'post_type_name'=>$this->post_type_name,

            'content'=>'',

            'status'=>'draft',

            'lang'=>$lang,

            'lang_parent_id'=>$this->getLangGroupId()

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLang(
        $query,
        string $lang
    ){
        return $query->where(
            'lang',
            $lang
        );
    }

    public function scopeCurrentLang($query)
    {
        return $query->where(
            'lang',
            app()->getLocale()
        );
    }

    public function scopeOriginal($query)
    {
        return $query->whereNull(
            'lang_parent_id'
        );
    }

    public function scopeTranslations($query)
    {
        return $query->whereNotNull(
            'lang_parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getLangAttribute(
        $value
    ){
        return $value
            ?? config('app.locale');
    }
}