<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Specific\OrderItem;
use App\Models\Specific\Seat;
use App\Models\Specific\Section;
use App\Models\Specific\Show;
use App\Models\Specific\Ticket;
use App\Models\Specific\VenueScheme;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VenueController extends Controller
{

    public function allSchemes() {
        $schemes = VenueScheme::all()->groupBy('venue_id')->all();
        return response()->json($schemes);
    }


    public function getScheme($id) {
        $scheme = VenueScheme::findOrFail($id);
        $scheme->sections;
        $scheme->venue;
        return response()->json($scheme);
    }


    public function save($id, Request $request) {
        $scheme = VenueScheme::findOrFail($id);
        $scheme->update($request->only('width', 'height', 'x', 'y'));
        foreach($request->sections as $section) {
            $data = [
                'title'           => $section['title'],
                'note'            => $section['note'],
                'venue_scheme_id' => $scheme->id,
                'svg'             => $section['svg'],
                'for_sale'        => $section['for_sale'],
                'entrance'        => $section['entrance'],
                'show_title'      => $section['show_title'],
            ];
            if(!isset($section['id'])) {
                Section::create($data);
            } else {
                Section::where('id',$section['id'])->where('venue_scheme_id', $scheme->id)->update($data);
            }
        }
        return $this->getScheme($id);
    }


    public function deleteSector($id) {
        $section = Section::findOrFail($id);
        $scheme_id = $section->venue_scheme_id;
        if($section->tickets()->count() < 1) {
            $section->delete();
        }
        return $this->getScheme($scheme_id);
    }

    public function saveSeats($id, Request $request) {
        foreach($request->seats as $seat) {
            if(!isset($seat['id'])) {
                Seat::create([
                    'section_id' => $id,
                    'x' => $seat['x'],
                    'y' => $seat['y'],
                    'row' => $seat['row'],
                    'seat' => $seat['seat'],
                ]);
            } else {
                Seat::where('id',$seat['id'])
                    ->where('section_id', $id)
                    ->update([
                        'x' => $seat['x'],
                        'y' => $seat['y'],
                        'row' => $seat['row'],
                        'seat' => $seat['seat'],
                    ]);
            }
        }
        return response()->json(Section::customDetails($id));
    }

    public function deleteSeats($id, Request $request) {
        if(Ticket::whereIn('seat_id', $request->seats)->count() > 0) {
            throw ValidationException::withMessages(['show_message' => 'Нельзя удалить места, на которые уже заведены билеты']);
        }
        if(OrderItem::whereIn('seat_id', $request->seats)->count() > 0) {
            throw ValidationException::withMessages(['show_message' => 'Нельзя удалить места, на которые уже были заказы']);
        }
        Seat::where('section_id',$id)->whereIn('id',$request->seats)->delete();
        return response()->json(Section::customDetails($id));
    }

    public function duplicateScheme($id, Request $request) {
        $venueScheme = VenueScheme::find($id);
        if(!$venueScheme || !$venueScheme->venue) return response()->json(false);
        $venueScheme->duplicate($request->title);
        return response()->json(true);
    }

    public function getSchemesForShow($id) {
        $show = Show::find($id);
        if(!$show || !$show->venue) {
            return response()->json([
                'schemes' => [],
                'venue_id' => null
            ]);
        }
        return response()->json([
            'schemes' => $show->venue->schemes()->get(),
            'venue_id'=> $show->venue_id
        ]);
    }

}
