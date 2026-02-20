<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Option;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Karbantartás oldal és admin útvonalak engedélyezése
        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        // Fájl alapú karbantartás ellenőrzése
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
        return File::exists(storage_path('framework/.maintenance'));
    }

    protected function maintenanceResponse(Request $request): Response
    {
        // Téma specifikus maintenance view keresése
        $currentThemeName = $this->getCurrentTheme();
        $viewFile = base_path("Themes/{$currentThemeName}/resources/views/maintenance.blade.php");

        // Téma Service Provider regisztrálása, ha létezik
        if (File::exists($viewFile) == true) {
            return response()
                ->view("theme::maintenance", [], 302)
                ->header('Retry-After', 3600);
        }

        // Alapértelmezett maintenance view
        return response()
            ->view('front.maintenance', [], 302)
            ->header('Retry-After', 3600);
    }

    protected function getCurrentTheme(): string
    {
        // Ha van OptionService vagy hasonló
        if (app()->bound('options.repository')) {
            return app('options.repository')->get('currentThemeName', 'FocusDefaultTheme');
        }

        // Visszaesés ha nincs options repository
        return Option::where('key', 'currentThemeName')->value('value') ?? 'FocusDefaultTheme';
    }
}