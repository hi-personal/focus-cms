<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostImageAlbum extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_image_albums';

    /**
     * timestamps
     *
     * @var undefined
     */

    public $timestamps = false;
    /**
     * fillable
     *
     * @var array
     */

    protected $fillable = [
        'post_id',
        'name',
        'title',
        'description',
    ];


    /**
     * Method post
     *
     * @return void
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Method postImages
     *
     * @return void
     */
    public function images()
    {
        return $this->belongsToMany(PostImage::class, 'post_image_album_relationships')
            ->withPivot('order')
            ->orderBy('order', 'asc');
    }
}
