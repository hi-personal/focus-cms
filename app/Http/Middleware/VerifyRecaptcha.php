<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldVerifyRecaptcha()) {
            return $next($request);
        }

        $token = $request->input('recaptcha_token');

        /*
        |--------------------------------------------------------------------------
        | SECURITY PATCH: explicit empty + type validation
        |--------------------------------------------------------------------------
        */
        if (!is_string($token) || trim($token) === '') {
            return $this->failedResponse('Hiányzó reCAPTCHA token');
        }

        try {

            $response = Http::asForm()
                ->timeout(5)
                ->retry(2, 100)
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret'   => config('recaptcha.secret_key'),
                        'response' => $token,
                        'remoteip' => $request->ip()
                    ]
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | SECURITY PATCH: prevent exception bypass
            |--------------------------------------------------------------------------
            */
            return $this->failedResponse('Captcha ellenőrzési hiba');
        }

        if (!$response->successful()) {
            return $this->failedResponse('A reCAPTCHA szolgáltatás nem elérhető');
        }

        $data = $response->json();

        /*
        |--------------------------------------------------------------------------
        | SECURITY PATCH: strict validation
        |--------------------------------------------------------------------------
        */
        if (
            empty($data)
            || empty($data['success'])
            || !isset($data['score'])
            || $data['score'] < config('recaptcha.threshold')
        ) {
            return $this->failedResponse('A reCAPTCHA ellenőrzés sikertelen');
        }

        return $next($request);
    }

    protected function shouldVerifyRecaptcha(): bool
    {
        if (!config('recaptcha.enabled')) {
            return false;
        }

        if (in_array(config('app.env'), config('recaptcha.skip_env'), true)) {
            return false;
        }

        return true;
    }

    protected function failedResponse(string $message)
    {
        if (request()->expectsJson()) {

            return response()->json(
                [
                    'message' => $message,
                    'errors'  => ['recaptcha' => [$message]]
                ],
                422
            );
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['recaptcha' => $message]);
    }
}