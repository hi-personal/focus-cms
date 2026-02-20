<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostFileRelationship extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_file_relationships';

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
        'post_file_id',
        'order'
    ];


    // Kapcsolódás a Post modelhez
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Method queryByPostId
     *
     * @param int $postId [explicite description]
     *
     * @return Builder
     */
    public static function queryByPostId(int $postId): Builder
    {
        return self::where('post_id', $postId)->with('file'); // Eloquent Query Builder marad
    }

    /**
     * Method image
     *
     * @return BelongsTo
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(PostFile::class, 'post_file_id');
    }


    /**
     * Method scopeQueryByPostId
     *
     * @param $query $query [explicite description]
     * @param $postId $postId [explicite description]
     *
     * @return void
     */
    public function scopeQueryByPostId($query, $postId)
    {
        return $query->where('post_id', $postId);
    }
}