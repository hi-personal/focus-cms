<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class VerifyCloudfareTurnstile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldVerifyTurnstile()) {
            return $next($request);
        }

        if (!$this->validateTurnstile($request)) {

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['captcha' => 'Captcha validáció sikertelen']);
        }

        return $next($request);
    }

    protected function validateTurnstile(Request $request): bool
    {
        try {

            $response = Http::asForm()
                ->timeout(5)
                ->retry(3, 1000)
                ->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    [
                        'secret'   => config('cloudflare.secret_key'),
                        'response' => $request->input('cf-turnstile-response'),
                        'remoteip' => $request->ip()
                    ]
                );

        } catch (\Throwable $e) {

            return false;
        }

        $data = $response->json();

        return !empty($data['success']);
    }

    protected function shouldVerifyTurnstile(): bool
    {
        if (!config('cloudflare.enabled')) {
            return false;
        }

        if (in_array(config('app.env'), config('cloudflare.skip_env'), true)) {
            return false;
        }

        return true;
    }
}