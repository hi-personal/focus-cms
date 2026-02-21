<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Option;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Allow bypass for specific routes
        |--------------------------------------------------------------------------
        */
        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Check maintenance flag file
        |--------------------------------------------------------------------------
        */
        if ($this->isFileBasedMaintenanceActive()) {
            return $this->maintenanceResponse($request);
        }

        return $next($request);
    }

    protected function shouldBypassMaintenance(Request $request): bool
    {
        return $request->is([
            'admin*',
            'login',
            'logout',
            'up',
        ]) || $request->routeIs('maintenance');
    }

    protected function isFileBasedMaintenanceActive(): bool
    {
        return File::exists(
            storage_path('framework/.maintenance')
        );
    }

    protected function maintenanceResponse(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | SECURITY PATCH: exception-safe theme detection
        |--------------------------------------------------------------------------
        */
        try {

            $currentThemeName = $this->getCurrentTheme();

        } catch (\Throwable $e) {

            $currentThemeName = 'FocusDefaultTheme';
        }

        $viewFile = base_path(
            "Themes/{$currentThemeName}/resources/views/maintenance.blade.php"
        );

        /*
        |--------------------------------------------------------------------------
        | SECURITY PATCH: correct HTTP status code (503)
        |--------------------------------------------------------------------------
        */
        if (File::exists($viewFile)) {

            return response()
                ->view("theme::maintenance", [], 503)
                ->header('Retry-After', 3600);
        }

        return response()
            ->view('front.maintenance', [], 503)
            ->header('Retry-After', 3600);
    }

    protected function getCurrentTheme(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Use options repository if available
        |--------------------------------------------------------------------------
        */
        if (app()->bound('options.repository')) {

            $theme = app('options.repository')->get(
                'currentThemeName',
                'FocusDefaultTheme'
            );

            if (is_string($theme) && $theme !== '') {
                return $theme;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback to database
        |--------------------------------------------------------------------------
        */
        $theme = Option::where('key', 'currentThemeName')
            ->value('value');

        if (is_string($theme) && $theme !== '') {
            return $theme;
        }

        /*
        |--------------------------------------------------------------------------
        | Final fallback
        |--------------------------------------------------------------------------
        */
        return 'FocusDefaultTheme';
    }
}