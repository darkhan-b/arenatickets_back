<?php

namespace App\Mail;

use App\Models\Specific\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $application;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Application $application) {
        $this->application = $application;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $application = $this->application;
        if($application->resume_path) {
            $this->attach(storage_path('app/'.$application->resume_path));
        }
        return $this
            ->markdown('emails.application')
            ->subject('Новая заявка на сайте '.env('APP_URL'))
            ->to(env('NOTIFICATION_EMAIL'))
            ->with([
                'application' => $application,
            ]);
    }
}
