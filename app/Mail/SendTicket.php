<?php

namespace App\Mail;

use App\Models\PDF\PDFTicket;
use App\Models\Specific\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTicket extends Mailable {

	use Queueable, SerializesModels;

	protected $order;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Order $order) {
		$this->order = $order;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		$order = $this->order;
		$pdf = new PDFTicket($order);
		$pdf = $pdf->pdf;
		if($pdf) {
			$timetable = $order->timetable;
			$venue = $timetable->venue;
			$event = $timetable->show;
			$isReservation = $order->reservation;
			$this->markdown('emails.tickets')
				->subject(($isReservation ? 'Бронирование на ' : 'Билеты на ').$event->title)
				->with([
					'isReservation' => $isReservation,
					'name'  		=> $order->name,
					'venue' 		=> $venue,
					'title' 		=> $event->title,
					'order' 		=> $order,
					'date'  		=> date('d.m.Y',strtotime($timetable->date)).' в '.date('H:i',strtotime($timetable->date))
				]);
			if(!$isReservation) {
				$this->attachData($pdf->output(), 'tickets.pdf', [
					'mime' => 'application/pdf',
				]);
			}
			return $this;
		}
	}
}
