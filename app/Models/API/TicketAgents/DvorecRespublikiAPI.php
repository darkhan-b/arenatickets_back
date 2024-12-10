<?php

namespace App\Models\API\TicketAgents;

class DvorecRespublikiAPI extends GeneralPremieraAPI {

    protected static $url = '95.56.228.2';
    protected static $port = '9194';
    protected static $service_id = '524135993';
    protected static $platform = API_DVOREC_RESPUBLIKI;
    protected static $venue_id = 14;
    protected static $venue_scheme_id = 16;
    protected static $default_category_id = 1;
    protected static $timetablesPricegroups = [];
    protected static $closedSectors = [];
//    protected static $timetablesSyncLimit = [457];
    
}