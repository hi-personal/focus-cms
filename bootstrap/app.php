<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\CheckMaintenanceMode::class
        );

        $middleware->alias([
            'setLocale'        => \App\Http\Middleware\SetLocale::class,
            'maintenance'      => \App\Http\Middleware\CheckMaintenanceMode::class,
            'validatePostType' => \App\Http\Middleware\ValidatePostType::class,
            'validateTaxonomy' => \App\Http\Middleware\ValidateTaxonomy::class,
            '2fa'              => \App\Http\Middleware\TwoFactorAuthMiddleware::class,
            'recaptcha'        => \App\Http\Middleware\VerifyRecaptcha::class,
            'cfTurnstile'      => \App\Http\Middleware\VerifyCloudfareTurnstile::class,
            'clear.page.cache' => \App\Http\Middleware\ClearPageCacheMiddleware::class,
        ]);

        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\TwoFactorAuthMiddleware::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\ValidatePostType::class,
            \App\Http\Middleware\ValidateTaxonomy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, $e) {
            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Túl sok kérés. Próbáld újra később.',
                    'retry_after' => $response->headers->get('Retry-After'),
                ], 429);
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e instanceof \Illuminate\Validation\ValidationException
                        ? 'Érvénytelen fájlformátum'
                        : 'Hiba történt a feltöltés során',
                    'url' => null
                ], $response->getStatusCode());
            }

            return $response;
        });
    })
    ->create();