<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PostImageAlbumRelationship extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_image_album_relationships';

    /**
     * primaryKey
     *
     * @var array
     */
    protected $primaryKey = ['post_image_album_id', 'post_image_id'];

    /**
     * incrementing
     *
     * @var bool
     */
    public $incrementing = false;

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
        'post_image_album_id',
        'post_image_id',
        'order'
    ];
}
