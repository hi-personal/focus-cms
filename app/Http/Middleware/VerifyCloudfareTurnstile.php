<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class VerifyCloudfareTurnstile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //return $next($request);

        if (!$this->shouldVerifyTurnstile()) {
            return $next($request);
        }

        if (!$this->validateTurnstile($request)) {
            return back()->withErrors(['captcha' => 'Captcha validáció sikertelen']);
        }

        return $next($request);
    }

    function validateTurnstile($request)
    {
        $response = Http::asForm()
            ->timeout(5)
            ->retry(3, 1000)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('cloudflare.secret_key'),
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip()
            ]
        );

        return $response->json()['success'] ?? false;
    }

    protected function shouldVerifyTurnstile(): bool
    {
        // Ha nincs engedélyezve a konfigban
        if (!config('cloudflare.enabled')) {
            return false;
        }

        // Ha a környezet benne van a kihagyandó listában
        if (in_array(config('app.env'), config('cloudflare.skip_env'))) {
            return false;
        }

        return true;
    }
}

