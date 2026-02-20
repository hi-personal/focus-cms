<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UserMeta extends Model
{
    use HasFactory;

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
        'user_id',
        'name',
        'value',
        'transient',
        'valid',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'transient' =>  false,
    ];


    /**
     * Method user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transient' => 'boolean',
            'valid'     => 'datetime',
        ];
    }

    public static function getMetaValue($userId, $name)
    {
        return optional(static::where('user_id', $userId)->where('name', $name)->first())->value;
    }

    public static function updateOrCreateMeta($userId, $name, $value)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'name' => $name],
            ['value' => $value]
        );
    }

    protected function getValueAttribute($value)
    {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }

        return $value;
    }

    public static function getDefaults()
    {
        return config('validation_rules.user_metas.default_values') ?? [];
    }

    protected function getDefaultValue()
    {
        $defaults = $this::getDefaults();

        return $defaults[$this->name] ?? null;
    }

    public static function find($userId, $metaName)
    {
        $um = new UserMeta();
        $res = $um::where('user_id', $userId)->where('name', $metaName)->get()->first();

        if(
            is_null($res)
            || (
                !empty($res->valid)
                && $res->valid < now()
            )
        ) {
            $defaults = $um::getDefaults();
            $um->value = $defaults[$metaName] ?? null;

            return $um;
        }

        return $res;
    }
}
