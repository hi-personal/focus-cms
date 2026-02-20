<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImageRelationship extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_image_relationships';

    /**
     * primaryKey
     *
     * @var array
     */
    protected $primaryKey = ['post_id', 'post_image_id'];

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
        'post_id',
        'post_image_id',
        'order'
    ];

    /**
     * Method queryByPostId
     *
     * @param int $postId [explicite description]
     *
     * @return Builder
     */
    public static function queryByPostId(int $postId): Builder
    {
        return self::where('post_id', $postId)->with('image'); // Eloquent Query Builder marad
    }


    /**
     * Method image
     *
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(PostImage::class, 'post_image_id');
    }

    public function scopeQueryByPostId($query, $postId)
    {
        return $query->where('post_id', $postId);
    }
}
