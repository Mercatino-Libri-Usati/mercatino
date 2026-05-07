<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RicevutaCreata extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfPath;

    public function __construct(string $pdfPath)
    {
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->view('emails.ricevuta-creata')
            ->subject('Nuova ricevuta creata sul tuo account')
            ->attachData(Storage::disk('public')->get($this->pdfPath), basename($this->pdfPath), [
                'mime' => 'application/pdf',
            ]);
    }
}
