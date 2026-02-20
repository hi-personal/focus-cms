<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use App\Models\UserSession;
use App\Models\Option;
use App\Models\User;
use App\Models\UserMeta;
use App\Http\Controllers\Traits\Auth2FaTrait;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


class AuthenticatedSessionController extends Controller
{
    use Auth2FaTrait;


    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if(!empty($user)) {
            $auth2FaStatus = UserMeta::find($user->id, 'auth_2fa_status')->value;
            $auth2FaMode = UserMeta::find($user->id, 'auth_2fa_mode')->value;

            if($request->password == "hidden") {
                if($auth2FaMode == "email") {
                    $this->sendTokenMail($user);
                }

                return view('auth.login', [
                    'email'         => $user->email,
                    'passwordInput' => true,
                    'auth2FaStatus' => $auth2FaStatus,
                    'auth2FaMode'   => $auth2FaMode,
                    'showSendmail'  => !empty(UserMeta::find($user->id, 'auth_2fa_temp_secret')->value)
                ]);
            }
        }

        //     $request->authenticate();


        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            return $this->backWithError($request, $user, [
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return $this->backWithError($request, $user, ['email' => __('These credentials do not match our records.')]);
        }

        $request->session()->regenerate();
        $request->session()->save();

        if (
            !empty($user)
            && $auth2FaStatus == true
        ) {
            $userSession = UserSession::find(
                $request->getSession()->getId()
            );

            $auth2FaAppSecret = UserMeta::find($user->id, 'auth_2fa_app_secret');
            $auth2FaTempSecret = UserMeta::find($user->id, 'auth_2fa_temp_secret');
            $auth2FaAppRecoveryHash = UserMeta::find($user->id, 'auth_2fa_app_recovery_hash')->value;

            if ($auth2FaMode == "email") {
                if (Hash::check($request->verify, $auth2FaTempSecret->value)) {
                    $userSession->auth_2fa_validated = true;
                    $userSession->save();

                    UserMeta::where('user_id', $user->id)->where('name', 'auth_2fa_temp_secret')->delete();
                } else {
                    return $this->backWithError($request, $user, ['email' => __('These credentials do not match our records.')]);
                }
            }

            if ($auth2FaMode == "2fa_app") {
                $verifyRecoveryKey = Hash::check($request->verify, $auth2FaAppRecoveryHash);
                if (
                    $this->verifyCode($auth2FaAppSecret->value, $request->verify) == true
                    || $verifyRecoveryKey == true
                ) {
                    $userSession->auth_2fa_validated = true;
                    $userSession->save();
                } else {
                    return $this->backWithError($request, $user, ['email' => __('These credentials do not match our records.')]);
                }
            }
        }

        if ($auth2FaMode == "2fa_app" && isset($verifyRecoveryKey )) {
            if ($verifyRecoveryKey == true) {
                $this->disable2Fa($user);

                return redirect()->route('profile.edit');
            }
        }

        $this->sendLoginSuccessMail($user, $request->ip());

        return redirect()->route('dashboard');
    }

    protected function backWithError($request, $user, $error)
    {
        RateLimiter::hit($this->throttleKey($request));

        $this->sendLoginErrorMail($user, $request->ip());

        return back()->withInput(
            $request->except(['password', 'verify'])
        )->withErrors(
            $error
        );
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userSession = UserSession::find(
            $request->getSession()->getId()
        );

        if(!empty($userSession)) {
            $userSession->auth_2fa_validated = false;
            $userSession->save();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function destroySession(Request $request, $sessionId): RedirectResponse
    {
        $session = UserSession::findOrFail($sessionId);

        if ($request->user()->id === $session->user_id) {
            $session->delete();
            return redirect()->back()->with('status', 'Session terminated.');
        }

        abort(403, 'Unauthorized action.');
    }

    private function sendTokenMail($user)
    {
        $auth2FaTempSecret = UserMeta::find($user->id, 'auth_2fa_temp_secret');

        if (is_null($auth2FaTempSecret) || $auth2FaTempSecret->valid < now()) {
            $auth2FaTempSecret = $this->generateSecret('email_login');

            UserMeta::updateOrCreate(
                ['user_id' => $user->id, 'name' => 'auth_2fa_temp_secret'],
                [
                    'value' => Hash::make($auth2FaTempSecret, ['rounds' => 12]),
                    'valid' => now()->addMinutes(10)
                ]
            );

            try {
                Mail::to($user->email)
                    ->send(new \App\Mail\TwoFactorEmail(
                        $auth2FaTempSecret,
                        null
                    )
                );
            } catch (\Exception $e) {
                return response('Hiba történt az email küldésekor: ' . $e->getMessage());
            }
        }
    }

    private function sendLoginSuccessMail($user, $ip)
    {
        try {
            Mail::to($user->email)
                ->send(new \App\Mail\LoginSuccessEmail(
                    $user,
                    $ip
                )
            );
        } catch (\Exception $e) {
            return response('Hiba történt az email küldésekor: ' . $e->getMessage());
        }
    }

    private function sendLoginErrorMail($user, $ip)
    {
        try {
            Mail::to($user->email)
                ->send(new \App\Mail\LoginErrorEmail(
                    $user,
                    $ip
                )
            );
        } catch (\Exception $e) {
            return response('Hiba történt az email küldésekor: ' . $e->getMessage());
        }
    }

    public function disable2Fa($user)
    {
        $user = Auth::user();

        UserMeta::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'auth_2fa_status'],
            ['value' => false]
        );

        UserMeta::where('user_id', $user->id)->where('name', 'auth_2fa_app_secret')->delete();
        UserMeta::where('user_id', $user->id)->where('name', 'auth_2fa_temp_secret')->delete();
        UserMeta::where('user_id', $user->id)->where('name', 'auth_2fa_app_recovery_hash')->delete();

        return redirect()->route('profile.edit')->with('status', '2FA kikapcsolva.');
    }
}
