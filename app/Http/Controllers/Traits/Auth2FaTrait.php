<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use ParagonIE\ConstantTime\Base32;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\Algorithm;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;


trait Auth2FaTrait
{
    protected function generateQRCode($email, $secret)
    {
        // Secret validálása
        if (!preg_match('/^[A-Z2-7]+$/', $secret)) {
            throw new \InvalidArgumentException('Érvénytelen Base32 secret');
        }

        // OTP URL összeállítása
        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            urlencode(config('app.name')),
            urlencode($email),
            $secret,
            urlencode(config('app.name'))
        );

        // QR kód generálása
        $renderer = new ImageRenderer(
            new RendererStyle(400, 2),  // Optimalizált méret
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url));
    }


    private function generateSecret($type = null)
    {
        $secret = match($type) {
            'email'       => Str::random(32),
            'email_login' => substr(str_shuffle(str_repeat('0123456789', 6)), 0, 6),
            '2fa_app'     => $this->generate2FAAppSecret()
        };

        return $secret;
    }

    private function generateRecoveryData()
    {
        $key = Str::random(32);
        $hash = Hash::make($key, ['rounds' => 12]);

        return (object) ['key' => $key, 'hash' => $hash];
    }

    private function generate2FAAppSecret()
    {
        $tfa = new TwoFactorAuth(
            new BaconQrCodeProvider(),
            config('app.name'),
            6,
            30,
            Algorithm::Sha1
        );

        return $tfa->createSecret();
    }

    protected function verifyCode(
        null|string $secret,
        int|string $verify_key
    ): bool {
        if (empty($secret))
            return false;

        $tfa = new TwoFactorAuth(
            new BaconQrCodeProvider(),
            config('app.name'),
            6,
            30,
            Algorithm::Sha1
        );

        return $tfa->verifyCode($secret, $verify_key);
    }
}