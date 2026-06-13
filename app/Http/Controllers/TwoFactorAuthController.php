<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\UserMeta;
use App\Http\Controllers\Traits\Auth2FaTrait;

class TwoFactorAuthController extends Controller
{
    private null|object $user;
    private null|string $auth2FaMode;
    private null|string $auth2FaStatus;
    private null|string $auth2FaAppSecret;
    private null|string|object $auth2FaTempSecret;

    use Auth2FaTrait;


    public function __construct()
    {
        $this->user = Auth::user();
        $this->auth2FaMode = UserMeta::find($this->user->id, 'auth_2fa_mode')->value;
        $this->auth2FaStatus = UserMeta::find($this->user->id, 'auth_2fa_status')->value;
        $this->auth2FaAppSecret = UserMeta::find($this->user->id, 'auth_2fa_app_secret')->value;
        $this->auth2FaTempSecret = UserMeta::find($this->user->id, 'auth_2fa_temp_secret');
    }

    public function auth2FaSetup($mode)
    {
        $method = "auth2FaSetup".Str::ucfirst($mode);
        if($this->auth2FaStatus == false && method_exists($this, $method)) {
            return $this->{$method}();
        }

        return redirect()->route('profile.edit');
    }

    public function auth2FaSetupStore(Request $request, $mode)
    {
        $method = "auth2FaSetupStore".Str::ucfirst($mode);
        if($this->auth2FaStatus == false && method_exists($this, $method)) {
            return $this->{$method}($request, $mode);
        }

        return redirect()->route('profile.edit');
    }

    private function auth2FaSetupEmail()
    {
        if (is_null($this->auth2FaTempSecret) || $this->auth2FaTempSecret->valid < now()) {
            $this->auth2FaTempSecret = $this->generateSecret('email');

            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_temp_secret'],
                [
                    'value' => $this->auth2FaTempSecret,
                    'valid' => now()->addMinutes(10)
                ]
            );
        } else {
            $this->auth2FaTempSecret = $this->auth2FaTempSecret->value;
        }

        $subject = '2FA Email Hitelesítés aktiválása';
        $body = 'Az egyszer használatos 2FA tokened: ' . $this->auth2FaTempSecret;

        //Send Email
        try {
            Mail::to($this->user->email)
                ->send(new \App\Mail\TwoFactorEmail(
                    $this->auth2FaTempSecret,
                    url()->query('/two-factor-auth-verify-email', ['token' => $this->auth2FaTempSecret])
                )
            );
        } catch (\Exception $e) {
            return response('Hiba történt az email küldésekor: ' . $e->getMessage());
        }

        //Show Mail sent message
        return view('auth.two-factor-setup-email', [
            'status'       => $this->auth2FaStatus,
            'mode'         => $this->auth2FaMode,
            'secret'       => $this->auth2FaTempSecret,
            'mail_address' => $this->user->email
        ]);
    }

    public function verifyíEmailToken(Request $request)
    {
        if ($request->token == $this->auth2FaTempSecret->value) {
            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_status'],
                ['value' => true]
            );

            UserMeta::where('user_id', $this->user->id)->where('name', 'auth_2fa_temp_secret')->delete();

            //Show Email address verifyed and email 2fa method activated message
            return view('auth.two-factor-setup-email', ['status' => true]);
        }

        return redirect()->route('profile.edit');
    }

    private function auth2FaSetup2FaApp()
    {
        if (
            empty($this->auth2FaTempSecret->value)
            || $this->auth2FaTempSecret->valid < now()
        ) {
            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_temp_secret'],
                [
                    'value' => $this->generateSecret('2fa_app'),
                    'valid' => now()->addMinutes(10)
                ]
            );

            $this->auth2FaTempSecret = UserMeta::find($this->user->id, 'auth_2fa_temp_secret');
        }

        return view('auth.two-factor-setup-2fa-app', [
            'status' => $this->auth2FaStatus,
            'mode'   => $this->auth2FaMode,
            'qrCode' => $this->generateQRCode($this->user->email, $this->auth2FaTempSecret->value),
            'secret' => $this->auth2FaTempSecret->value,
        ]);
    }

    private function auth2FaSetupStore2FaApp($request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'otp' => 'required|string',
            ]);

            if (
                empty($this->auth2FaTempSecret->value)
                || $this->auth2FaTempSecret->valid < now()
                || $this->verifyCode($this->auth2FaTempSecret->value, $request->otp) == false
            ) {
                return back()->withErrors(['otp' => 'Érvénytelen hitelesítő kód.']);
            }

            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_status'],
                ['value' => true]
            );

            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_app_secret'],
                [
                    'value' => $this->auth2FaTempSecret->value,
                    'valid' => null
                ]
            );

            $recoveryData = $this->generateRecoveryData();

            UserMeta::updateOrCreate(
                ['user_id' => $this->user->id, 'name' => 'auth_2fa_app_recovery_hash'],
                [
                    'value' => $recoveryData->hash,
                    'valid' => null
                ]
            );

            UserMeta::where('user_id', $this->user->id)->where('name', 'auth_2fa_temp_secret')->delete();

            return view('auth.two-factor-setup-2fa-app', [
                'status'      => true,
                'mode'        => '2FaApp',
                'qrCode'      => null,
                'secret'      => null,
                'recoveryKey' => $recoveryData->key,
            ]);
        }

        return redirect()->route('profile.edit');
    }

    public function disable()
    {
        $user = Auth::user();

        UserMeta::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'auth_2fa_status'],
            ['value' => false]
        );

        UserMeta::where('user_id', $this->user->id)->where('name', 'auth_2fa_app_secret')->delete();
        UserMeta::where('user_id', $this->user->id)->where('name', 'auth_2fa_temp_secret')->delete();
        UserMeta::where('user_id', $this->user->id)->where('name', 'auth_2fa_app_recovery_hash')->delete();

        return redirect()->route('profile.edit')->with('status', '2FA kikapcsolva.');
    }
}
