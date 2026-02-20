<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

class ResetPasswordCommand extends Command
{
    protected $signature = 'user:resetPass {email} {--show}';
    protected $description = 'Reset user password';

    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error("User not found!");
            return 1;
        }

        $newPassword = Str::random(12);
        $user->password = Hash::make($newPassword);
        $user->save();

        if ($this->option('show')) {
            $this->info("Password reset for {$user->email}");
            $this->line("New password: {$newPassword}");
        } else {
            Mail::to($user->email)->send(new PasswordResetMail($newPassword));
            $this->info("Password reset and emailed to {$user->email}");
        }

        return 0;
    }
}