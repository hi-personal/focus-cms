<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserMeta;

class Disable2FACommand extends Command
{
    protected $signature = 'user:disable2Fa {email}';
    protected $description = 'Disable 2FA for a user';

    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error("User not found!");
            return 1;
        }

        UserMeta::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'auth_2fa_status'],
            ['value' => false]
        );

        UserMeta::where('user_id', $user->id)
            ->whereIn('name', [
                'auth_2fa_app_secret',
                'auth_2fa_temp_secret',
                'auth_2fa_app_recovery_hash'
            ])->delete();

        $this->info("2FA disabled for {$user->email}");
        return 0;
    }
}