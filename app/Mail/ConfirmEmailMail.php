<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $email;
    protected $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(String $email, String $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->email;
        $code = $this->code;
        return $this
            ->markdown('emails.confirm_email')
            ->to($email)
            ->subject('Arenatickets - Подтверждение почты')
            ->with(compact('code'));
    }
}
