<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class ClearPageCacheMiddleware
{
    /**
     * Handle
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Locale from URL
        |--------------------------------------------------------------------------
        */

        $supportedLocales = config('app.supported_locales');

        $segments =
            $request->segments();

        $locale =
            $segments[0] ?? null;

        if (
            $locale
            &&
            in_array(
                $locale,
                $supportedLocales
            )
        ) {

            app()->setLocale(
                $locale
            );

        } else {

            app()->setLocale(
                config('app.locale')
            );

        }

        /*
         * normál response
         */
        $response =
            $next($request);

        /*
         * csak write műveletek
         */
        if (
            !in_array(
                $request->method(),
                [
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE'
                ]
            )
        ) {

            return $response;

        }

        /*
         * cache management route-ok kihagyása
         */
        if (
            $request->is('admin/cache/*')
        ) {

            return $response;

        }

        /*
         * csak sikeres response
         */
        if (
            $response->getStatusCode() >= 400
        ) {

            return $response;

        }

        /*
         * cache path
         */
        $cachePath =
            public_path('page-cache');

        /*
         * csak létező mappa esetén
         */
        if (
            !File::isDirectory($cachePath)
        ) {

            return $response;

        }

        /*
         * teljes cache törlés
         */
        File::cleanDirectory(
            $cachePath
        );

        return $response;
    }
}