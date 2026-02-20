<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTaxonomy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedTaxanomies = config('taxonomies');
        $allowedTaxanomyNames = array_keys($allowedTaxanomies);

        // A route paraméterből kinyerjük a post_type értéket
        $taxonomyName = $request->route('taxonomy_name');

        // Ha nincs az engedélyezett listában, irányítsuk át a dashboard-ra
        if (!in_array($taxonomyName, $allowedTaxanomyNames, true)) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
