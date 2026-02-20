<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Option extends Model
{
    use HasFactory;

    protected $primaryKey = 'name';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $fillable = [
        'name',
        'value',
        'transient',
        'valid',
    ];

    protected $attributes = [
        'value'     => null,
        'transient' => false,
        'valid'     => null,
    ];

    protected function casts(): array
    {
        return [
            'transient' => 'boolean',
            'valid' => 'datetime',
        ];
    }

    public function scopeGetValue($query, string $name)
    {
        return $query->where('name', $name)->value('value');
    }


    protected function getValueAttribute($value)
    {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }

        // Ha a value már objektum/tömb (pl. cache-ből jön)
        if (is_object($value) || is_array($value)) {
            return $value;
        }

        // Próbáljuk JSON-ként értelmezni
        $decoded = json_decode($value, false);

        // Ha érvényes JSON, visszaadjuk dekódolva, különben az eredeti stringet
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    protected function setValueAttribute($value)
    {
        // NULL érték kezelése
        if (is_null($value)) {
            $this->attributes['value'] = null;
            return;
        }

        // Ha már JSON string (pl. form request-ből)
        if (is_string($value) && $this->isValidJson($value)) {
            $this->attributes['value'] = $value;
            return;
        }

        // Ha objektum/tömb, JSON-né alakítjuk
        if (is_object($value) || is_array($value)) {
            $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
            return;
        }

        // Egyéb esetben (simpe string) közvetlenül elmentjük
        $this->attributes['value'] = $value;
    }

    private function isValidJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    public function getTransientAttribute($value)
    {
        return $value ?? false;
    }

    public function getValidAttribute($value)
    {
        return $value ?? $this->getDefaultValidDate();
    }

    public static function getDefaults()
    {
        return config('validation_rules.options.default_values') ?? [];
    }

    protected function getDefaultValue()
    {
        $defaults = $this::getDefaults();

        return $defaults[$this->name] ?? null;
    }

    protected function getDefaultValidDate()
    {
        return now()->addHour();
    }

    // Statikus metódus a könnyű hozzáféréshez
    public static function firstWithDefaults($name)
    {
        return static::firstOrNew(['name' => $name]);
    }
}