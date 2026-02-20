<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTermRelationship extends Model
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
        'post_id',
        'post_term_id',
    ];


    // Kapcsolódás a Post modelhez
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Kapcsolódás a PostTerm modelhez
    public function postTerm()
    {
        return $this->belongsTo(PostTerm::class, 'post_term_id');
    }

    /**
     * Visszaadja a kapcsolódó Post rekordot.
     *
     * @return \App\Models\Post
     */
    public function getPost(): Post
    {
        return $this->post; // Az Eloquent kapcsolat automatikusan tölti be
    }

    /**
     * Visszaadja a kapcsolódó PostTerm rekordot.
     *
     * @return \App\Models\PostTerm
     */
    public function getPostTerm(): PostTerm
    {
        return $this->postTerm; // Az Eloquent kapcsolat automatikusan tölti be
    }
}
