<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMeta;
use App\Models\UserSession;

class TwoFactorAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $twoFaStatus = UserMeta::find($user->id, 'auth_2fa_status')->value;

        if ($twoFaStatus != '1') {
            return $next($request);
        }

        $userSession = UserSession::find(
            $request->getSession()->getId()
        );

        if (!empty($userSession) && $userSession->auth_2fa_validated == true) {
            return $next($request);
        }

        if(
            $request->is([
                'login',
                'logout',
                'up',
            ])
            || $request->routeIs('password.request')
        ) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->withInput(['email' => $user->email]);
    }
}