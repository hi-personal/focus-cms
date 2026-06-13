<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;

class PostImage extends Model
{
    use HasFactory;
    use EnsureUniqueTitleAndNameTrait;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_images';

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
        'name',
        'title',
        'file_uri',
        'file_url',
        'file_extension',
        'mime_type',
        'file_size',
        'width',
        'height'
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
     * Method metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(PostImageMeta::class);
    }

    /**
     * Method meta
     *
     * @param string $key [explicite description]
     *
     * @return void
     */
    public function meta(string $name)
    {
    $meta = PostImageMeta::where('post_image_id', $this->id)->where('name', $name)->first();
        return $meta ? $meta->value : null;
    }

    /**
     * Method sizes
     *
     * @return HasMany
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(PostImageSize::class);
    }

    /**
     * Method posts
     *
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_image_relationships', 'post_image_id', 'post_id');
    }

    /**
     * Method albums
     *
     * @return BelongsToMany
     */
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(PostImageAlbum::class, 'post_image_album_relationships', 'post_image_id', 'post_image_album_id');
    }

    public function getImageUrl($size = 'thumbnail')
    {
        // Ha az 'original' méretet kérték, az eredeti képet adjuk vissza
        if ($size === 'original') {
            return asset($this->file_url);
        }

        // Méretek lekérése a konfigurációból
        $sizes = array_keys(config('media.image_sizes', [])); // Pl.: ['thumbnail', 'medium', 'large']
        $sizeIndex = array_search($size, $sizes);

        // Ha a méret nem érvényes, visszaadjuk az eredeti képet
        if ($sizeIndex === false) {
            return asset($this->file_url);
        }

        // Ellenőrizzük a méreteket a prioritási sorrendben
        for ($i = $sizeIndex; $i < count($sizes); $i++) {
            $currentSize = $sizes[$i];
            $sizeImage = $this->sizes()->where('name', $currentSize)->first();

            if ($sizeImage) {
                return asset($sizeImage->file_url);
            }
        }

        // Ha egyik méret sem létezik, visszaadjuk az eredeti képet
        return asset($this->file_url);
    }


    public function deleteImage()
    {
        $image = $this::find($this->id);

        if (!empty($image)) {
            $originalUri = public_path($image->file_uri);

            if (file_exists($originalUri)) {
                unlink($originalUri);
            }

            $sizes = $image->sizes()->get();

            if (!empty($sizes)) {
                foreach ($sizes as $size) {
                    $sizeUri = public_path($size->file_uri);

                    if (file_exists($sizeUri)) {
                        unlink($sizeUri);
                    }
                }
            }

            PostImageRelationship::where('post_image_id', $image->id)->delete();

            $this::where('id', $image->id)->delete();

            PostImageSize::where('post_image_id', $image->id)->delete();

           return "THIS ID: ".$this->id."_IMAGE ID: ".$image->id;
        }
    }
}
