<?php

namespace App\Models\Helpers;

use App\Models\Specific\SectionWithoutSeatSelection;
use App\Models\Specific\Timetable;

class SeatPicker {

	public static function pickSeatsForSectionsWithoutSeatSelection($cart, Timetable $timetable) {
		$sectionsWithoutSeatSelection = SectionWithoutSeatSelection::where('timetable_id', $timetable->id)->pluck('section_id')->toArray();
		$seatsToPick = [];
		if(count($sectionsWithoutSeatSelection)) {
			foreach($cart as $index => $ticket) { // here we count how many tickets we need to pick for each section and also save their indexes in cart
				if(isset($ticket['section_id']) && $ticket['section_id']) {
					$sectionId = $ticket['section_id'];
					if(in_array($sectionId, $sectionsWithoutSeatSelection)) {
						if(!isset($seatsToPick[$sectionId])) $seatsToPick[$sectionId] = [];
						$seatsToPick[$sectionId][] = $index;
					}
				}
			}
			foreach($seatsToPick as $sectionId => $indexes) {
				$ticketsInLine = self::getSeatsInARow($sectionId, count($indexes), $timetable);
				if(count($ticketsInLine) == count($indexes)) {
					foreach($indexes as $key => $value) {
						$ticket = $ticketsInLine[$key];
						$cart[$value] = [
							'ticket_id' 	=> $ticket->id,
							'section_id' 	=> $ticket->section_id,
							'row' 			=> $ticket->row,
							'price' 		=> $ticket->price,
							'seat' 			=> $ticket->seat,
							'seat_id' 		=> $ticket->seat_id,
						];
					}
				} else {
					$cart = []; // if even for one section we do not find all the tickets, we just nullify whole order
				}
			}
		}
		return $cart;
	}

	public static function getSeatsInARow($sectionId, $amount, $timetable) {
		$ticketsRows = $timetable->tickets()
			->available()
			->where('section_id', $sectionId)
			->orderBy('row', 'asc')
			->orderByRaw('CONVERT(seat, SIGNED) asc')
			->get()
			->groupBy('row');
		$ticketsInLine = [];
		foreach($ticketsRows as $row => $tickets) {
			foreach($tickets as $ticket) {
				if(count($ticketsInLine) === $amount) {
					break;
				}
				if(empty($ticketsInLine)) {
					$ticketsInLine[] = $ticket;
					continue;
				}
				$numSeat = (int)$ticket->seat;
				$previousNumSeat = (int)$ticketsInLine[count($ticketsInLine) - 1]->seat;
				if(($numSeat - 1) === $previousNumSeat) {
					$ticketsInLine[] = $ticket;
				} else {
					$ticketsInLine = [$ticket];
				}
			}
			if(count($ticketsInLine) === $amount) {
				break;
			} else {
				$ticketsInLine = [];
			}
		}
		return $ticketsInLine;
	}

}
