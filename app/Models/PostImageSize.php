<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImageSize extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_image_sizes';

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
        'post_image_id',
        'name',
        'file_uri',
        'file_url',
        'mime_type',
        'file_size',
        'width',
        'height'
    ];

    /**
     * Method image
     *
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(
            PostImage::class,
            'post_image_id',  // Foreign key a post_image_metas táblában
            'id'              // Local key a post_images táblában
        );
    }

    /**
     * Get the image size details from the configuration.
     *
     * @return object|null
     */
    public function getSizeConfigAttribute(): ?object
    {
        $sizeConfig = config("media.image_sizes.{$this->name}");

        return $sizeConfig ? (object) $sizeConfig : null;
    }

}