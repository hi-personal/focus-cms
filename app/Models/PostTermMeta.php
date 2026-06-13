<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PostTermMeta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'post_term_id',
        'locale',
        'name',
        'value',
    ];

    protected $casts = [
        'post_term_id' => 'integer'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function term(): BelongsTo
    {
        return $this->belongsTo(
            PostTerm::class,
            'post_term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLocale(
        Builder $query,
        ?string $locale = null
    ): Builder {

        $locale ??= config('app.locale');

        return $query->where(function ($q) use ($locale) {

            $q->where('locale', $locale)
              ->orWhereNull('locale');

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get localized meta value
     */
    public static function getValue(
        int $termId,
        string $key,
        ?string $locale = null,
        bool $default = true
    ): ?string {

        $locale ??= app()->getLocale();

        $defaultLocale=config('app.locale');

        $query=static::where(
            'post_term_id',
            $termId
        )
        ->where(
            'name',
            $key
        );

        /*
        |--------------------------------------------------------------------------
        | Only exact locale
        |--------------------------------------------------------------------------
        */

        if(!$default){

            return $query
                ->where(
                    'locale',
                    $locale
                )
                ->value('value');

        }

        /*
        |--------------------------------------------------------------------------
        | Locale fallback chain
        |--------------------------------------------------------------------------
        */

        return $query
            ->where(function ($q) use (
                $locale,
                $defaultLocale
            ) {

                /*
                |--------------------------------------------------------------------------
                | Requested locale
                |--------------------------------------------------------------------------
                */

                $q->where(
                    'locale',
                    $locale
                );

                /*
                |--------------------------------------------------------------------------
                | Default app locale fallback
                |--------------------------------------------------------------------------
                */

                if($defaultLocale !== $locale){

                    $q->orWhere(
                        'locale',
                        $defaultLocale
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | Global fallback
                |--------------------------------------------------------------------------
                */

                $q->orWhereNull('locale');

            })
            ->orderByRaw(
                "
                CASE
                    WHEN locale = ? THEN 1
                    WHEN locale = ? THEN 2
                    WHEN locale IS NULL THEN 3
                    ELSE 4
                END
                ",
                [
                    $locale,
                    $defaultLocale
                ]
            )
            ->value('value');
    }

    /**
     * Set localized meta value
     */
    public static function setValue(
        int $termId,
        string $key,
        ?string $value,
        ?string $locale = null
    ): void {

        $locale ??= config('app.locale');

        /*
        |--------------------------------------------------------------------------
        | Normalize empty values
        |--------------------------------------------------------------------------
        */

        if(
            $value !== null
            && trim($value)===''
        ){
            $value=null;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing meta
        |--------------------------------------------------------------------------
        */

        $meta=static::where(
            'post_term_id',
            $termId
        )
        ->where(
            'name',
            $key
        )
        ->where(
            'locale',
            $locale
        )
        ->first();

        /*
        |--------------------------------------------------------------------------
        | Update existing
        |--------------------------------------------------------------------------
        */

        if($meta){

            $meta->update([
                'value'=>$value
            ]);

            return;

        }

        /*
        |--------------------------------------------------------------------------
        | Do not create empty records
        |--------------------------------------------------------------------------
        */

        if($value===null){
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create new
        |--------------------------------------------------------------------------
        */

        static::create([
            'post_term_id'=>$termId,
            'name'=>$key,
            'locale'=>$locale,
            'value'=>$value
        ]);
    }

    /**
     * Set global/non-localized meta value
     */
    public static function setGlobalValue(
        int $termId,
        string $key,
        ?string $value
    ): void {

        static::updateOrCreate(
            [
                'post_term_id' => $termId,
                'locale' => null,
                'name' => $key
            ],
            [
                'value' => $value
            ]
        );
    }

    /**
     * Get all localized metas
     */
    public static function getAll(
        int $termId,
        ?string $locale = null
    )
    {
        $locale ??= config('app.locale');

        return static::where(
            'post_term_id',
            $termId
        )
        ->where(function ($q) use ($locale) {

            $q->where('locale', $locale)
              ->orWhereNull('locale');

        })
        ->orderByRaw(
            'locale IS NULL'
        )
        ->get()
        ->unique('name')
        ->pluck(
            'value',
            'name'
        );
    }

    /**
     * Get value without locale fallback
     */
    public static function getRawValue(
        int $termId,
        string $key,
        ?string $locale = null
    ): ?string {

        $locale ??= config('app.locale');

        return static::where(
            'post_term_id',
            $termId
        )
        ->where(
            'locale',
            $locale
        )
        ->where(
            'name',
            $key
        )
        ->value(
            'value'
        );
    }
}