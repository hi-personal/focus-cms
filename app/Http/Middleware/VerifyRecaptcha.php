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
        //return $next($request);
        // Ha nincs engedélyezve vagy teszt környezetben vagyunk
        if (!$this->shouldVerifyRecaptcha()) {
            return $next($request);
        }

        // if (!$request->is('login') || $request->filled('password') && $request->input('password') === 'hidden') {
        //     return $next($request);
        // }


        $token = $request->input('recaptcha_token');

        // Token ellenőrzése
        if (empty($token)) {
            return $this->failedResponse('Hiányzó reCAPTCHA token');
        }

        $response = Http::asForm()->timeout(5)->retry(2, 100)->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => config('recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip()
            ]
        );

        if (!$response->successful()) {
            return $this->failedResponse('A reCAPTCHA szolgáltatás nem elérhető');
        }

        $data = $response->json();

        if (!$data['success'] || $data['score'] < config('recaptcha.threshold')) {
            return $this->failedResponse('A reCAPTCHA ellenőrzés sikertelen');
        }

        return $next($request);
    }

    protected function shouldVerifyRecaptcha(): bool
    {
        // Ha nincs engedélyezve a konfigban
        if (!config('recaptcha.enabled')) {
            return false;
        }

        // Ha a környezet benne van a kihagyandó listában
        if (in_array(config('app.env'), config('recaptcha.skip_env'))) {
            return false;
        }

        return true;
    }

    protected function failedResponse(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['recaptcha' => [$message]]
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['recaptcha' => $message]);
    }
}