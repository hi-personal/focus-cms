<?php

// app/Rules/RecaptchaRule.php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class RecaptchaRule implements Rule
{
    // app/Rules/RecaptchaRule.php
    public function passes($attribute, $value)
    {
        // Helyi fejlesztés kihagyása
        if (config('recaptcha.skip') || app()->environment('local')) {
            return true;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip()
        ]);

        $data = $response->json();

        return $data['success'] && $data['score'] >= config('recaptcha.threshold');
    }

    public function message()
    {
        return 'A reCAPTCHA ellenőrzés sikertelen. Kérjük próbálja újra.';
    }
}