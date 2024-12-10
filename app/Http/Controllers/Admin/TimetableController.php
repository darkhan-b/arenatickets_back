<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\API\TicketAgents\AlmatyArenaAPI;
use App\Models\Specific\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{

    public function get($id) {
        $timetable = Timetable::with(['show', 'pricegroups', 'venue', 'scheme', 'scheme.sections'])
            ->findOrFail($id);
        $timetable->append('datePlaceString');
        $venue = $timetable->venue;
        $scheme = $timetable->scheme;
        $tickets = $timetable->groupedCountTickets(true);
        return response()->json([
            'timetable'                         => $timetable,
            'tickets'                           => $tickets,
            'venue'                             => $venue,
            'scheme'                            => $scheme,
            'closedSections'                    => $timetable->closedSections,
            'sectionsWithoutSeatSelections'     => $timetable->sectionsWithoutSeatSelections
        ]);
    }

    public function setType($id, Request $request) {
        $request->validate([
            'type' => ['required', 'in:pricegroups,sections']
        ]);
        $timetable = Timetable::findOrFail($id);
        $timetable->type = $request->type;
        $timetable->save();
        return response()->json(true);
    }

	public function syncShows() {
		AlmatyArenaAPI::synchronizeGeneral(false);
		return response()->json(true);
	}



}
