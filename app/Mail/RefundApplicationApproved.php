<?php

namespace App\Mail;

use App\Models\Specific\RefundApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    protected $application;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RefundApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $application = $this->application;
        return $this
            ->markdown('emails.refund_application_approved')
            ->to($application->email)
            ->bcc(env('NOTIFICATION_EMAIL'))
            ->subject('Заявка на возврат по заказу '.$application->order_id.' одобрена')
            ->with([
                'name' => $application->name,
                'orderId'   => $application->order_id,
            ]);
    }
}
