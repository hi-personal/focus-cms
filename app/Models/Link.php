<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'image',
        'target',
        'description',
        'visible',
        'user_id',
        'rating',
        'created_at',
        'updated_at',
        'rel',
        'notes',
        'rss',
    ];
}
