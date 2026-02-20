<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StripTags implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Engedélyezett címkék (opcionális)
        $allowedTags = '<strong><em>';

        if (strip_tags($value, $allowedTags) !== $value) {
            $fail('A(z) :attribute érvénytelen HTML címkéket tartalmaz.');
        }
    }
}
