<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePostType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Az engedélyezett post típusok betöltése a konfigból
        $allowedPosts = array_keys(config('post_types'));

        // A route paraméterből kinyerjük a post_type értéket
        $postType = $request->route('post_type');

        // Ha nincs az engedélyezett listában, irányítsuk át a dashboard-ra
        if (!in_array($postType, $allowedPosts, true)) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
