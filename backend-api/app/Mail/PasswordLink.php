<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordLink extends Mailable
{
    use Queueable, SerializesModels;

    public $link;

    public function __construct(string $token, string $email)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $this->link = $frontendUrl.'/imposta-password?token='.$token.'&email='.urlencode($email);
    }

    public function build()
    {
        return $this->subject('Imposta la tua password - Libri Usati Crema')
            ->view('emails.password-link')
            ->with(['link' => $this->link]);
    }
}
