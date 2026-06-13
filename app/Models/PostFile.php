<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\EnsureUniqueTitleAndNameTrait;

class PostFile extends Model
{
    use HasFactory;
    use EnsureUniqueTitleAndNameTrait;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'post_files';

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
        'file_size'
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
        return $this->hasMany(PostFileMeta::class);
    }

    /**
     * Method posts
     *
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_file_relationships', 'post_file_id', 'post_id');
    }

    /**
     * Method deleteFile
     *
     * @return void
     */
    public function deleteFile()
    {
        $file = $this::find($this->id);

        if (!empty($file)) {
            $originalUri = public_path($file->file_uri);

            if (file_exists($originalUri)) {
                unlink($originalUri);
            }

            PostFileRelationship::where('post_file_id', $file->id)->delete();

            $this::where('id', $file->id)->delete();

            return "THIS ID: ".$this->id."_FILE ID: ".$file->id;
        }
    }

    /**
     * Method getFileUrl
     *
     * @return void
     */
    public function getFileUrl()
    {
        return asset($this->file_url);
    }
}