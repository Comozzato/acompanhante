<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{

    use Queueable, SerializesModels;

    public $code;

    public function __construct(string $code)
    {
        $this->code = $code;

    }

    public function build()
    {
        return $this->subject('esqueceu a senha')
            ->view('emails.forgot_password')
            ->with(['code' => $this->code]);
    }
}
