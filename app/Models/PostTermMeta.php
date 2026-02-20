<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTermMeta extends Model
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
        'post_term_id',
        'name',
        'value',
    ];

    /**
     * Method postTerm
     *
     * @return void
     */
    public function term()
    {
        return $this->belongsTo(PostTerm::class, 'post_term_id', 'id');
    }
}
