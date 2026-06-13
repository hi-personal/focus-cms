<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function showMailForm()
    {
        return view('admin.mail.mail-form');
    }

    public function sendMail(Request $request)
    {
        // Validálás
        $validated = $request->validate([
            'mail_to' => 'required|email',
            'mail_subject' => 'required|string|max:255',
            'mail_body' => 'required|string',
        ]);

        // Adatok kinyerése
        $to = $validated['mail_to'];
        $subject = $validated['mail_subject'];
        $body = $validated['mail_body'];

        // Email küldése
        try {
            Mail::mailer('smtp')->send('admin.mail.mail-body', [
                'subject' => $subject,
                'body' => $body
            ], function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });

            return back()->with('success', 'Email sikeresen elküldve!');
        } catch (\Exception $e) {
            return back()->with('error', 'Hiba történt az email küldésekor: ' . $e->getMessage());
        }
    }
}