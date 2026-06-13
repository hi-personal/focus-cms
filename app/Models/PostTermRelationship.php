<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTermRelationship extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'post_term_id',
    ];

    protected $casts = [
        'post_id'=>'integer',
        'post_term_id'=>'integer'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(
            Post::class,
            'post_id'
        );
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(
            PostTerm::class,
            'post_term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (optional but useful)
    |--------------------------------------------------------------------------
    */

    public function taxonomy(): string
    {
        return $this->term->post_taxonomy_name;
    }

    public function termName(): string
    {
        return $this->term->name;
    }

    public function termTitle(): string
    {
        return $this->term->title;
    }
}