<?php

namespace App\Models\API\TicketAgents;

use App\Models\Specific\Timetable;

class AlmatyArenaAPI extends GeneralPremieraAPI {

    protected static $url = '89.219.33.138';
    protected static $port = '8090';
//    protected static $service_id = '392756746';
    protected static $service_id = '420176951';
    protected static $platform = API_ALMATY_ARENA;
    protected static $venue_id = 8;
    protected static $venue_scheme_id = 15;
    protected static $default_category_id = 1;
    protected static $timetablesPricegroups = [];
    protected static $closedSectors = [];
//    protected static $timetablesSyncLimit = [8886]; // testing only
    protected static $xOffset = 60;
    protected static $yOffset = 0;

//    public static function sectionIsEnterSection(Timetable $timetable, $local_section_id, $sectionsWithoutSeatsIds = null) {
//        if(in_array($local_section_id, [233,272,430,1059,1060])) {
//            return true;
//        }
//        return false;
//    }

}
