<?php

namespace App\Models\Specific;

class TicketsManipulator
{
    public static function moveAllTicketsAndSalesFromOneSchemeToAnother($timetableId, $newSchemeId) {
        $timetable = Timetable::findOrFail($timetableId);
        $newScheme = VenueScheme::findOrFail($newSchemeId);
        $oldScheme = $timetable->scheme;
        $show = $timetable->show;
        $tickets = $timetable->tickets;
        $sectionMapping = self::getSectionMappingBetweenTwoSchemes($oldScheme, $newScheme);
        if(!$show || !$oldScheme) return false;
        // part 1: change seat_id and section_id in tickets and order items //
        foreach($tickets as $t) {
            $newSectionId = $t->section_id;
            $newSeatId = $t->seat_id;
            if($t->section_id) {
                $newSectionId = $sectionMapping[$t->section_id] ?? $t->section_id;
            }
            if($t->seat_id && $newSectionId) {
                $newSeat = Seat::where([
                    'section_id' => $newSectionId, 
                    'row' => $t->row, 
                    'seat' => $t->seat
                ])->first();
                if($newSeat) $newSeatId = $newSeat->id;
            }
            $updateData = [
                'section_id' => $newSectionId,
                'seat_id'    => $newSeatId
            ];
            $t->update($updateData);
            foreach($t->orderItems as $oi) {
                $oi->update($updateData);    
            }
        }
        
        // part 2: change timetable venue_id and venue_scheme_id //
        $timetable->update([
            'venue_id'          => $newScheme->venue_id,
            'venue_scheme_id'   => $newScheme->id
        ]);
        
        // part 3: change show venue_id and venue_scheme_id //
        $show->update(['venue_id' => $newScheme->venue_id]);
        return true;
    }
    
    public static function getSectionMappingBetweenTwoSchemes(VenueScheme $scheme1, VenueScheme $scheme2) {
        $res = [];
        foreach($scheme1->sections as $section) {
            $section2 = $scheme2->sections()->where('title->ru', $section->title)->first();
            if($section2) {
                $res[$section->id] = $section2->id; 
            }
        }
        return $res;
    }
}