<?php

namespace App\Mail;

use App\Models\General\User;
use App\Models\Specific\Show;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizerAddedEvent extends Mailable
{
    use Queueable, SerializesModels;

    protected $show;
    protected $organizer;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Show $show, User $organizer)
    {
        $this->show = $show;
        $this->organizer = $organizer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $organizer = $this->organizer;
        $show = $this->show;
        return $this
            ->markdown('emails.organizer_created_event')
            ->to(env('NOTIFICATION_EMAIL'))
            ->bcc(env('DEVELOPER_NOTIFICATION_EMAIL'))
            ->subject('Событие создано организатором')
            ->with(compact('show','organizer'));
    }
}
