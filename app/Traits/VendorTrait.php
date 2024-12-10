<?php

namespace App\Traits;

use App\Models\API\TicketAgents\AbaiSemeyLentaAPI;
use App\Models\API\TicketAgents\AlmatyArenaAPI;
use App\Models\API\TicketAgents\DvorecRespublikiAPI;
use App\Models\API\TicketAgents\ShowMarketAPI;
use App\Models\API\TicketAgents\TicketAPIInterface;

trait VendorTrait {

    public function getVendorClass() : ?TicketAPIInterface {
        if(!$this->vendor) return null;
        $arr = [
            API_DVOREC_RESPUBLIKI   => DvorecRespublikiAPI::getInstance(),
            API_ALMATY_ARENA        => AlmatyArenaAPI::getInstance(),
            API_SEMEY_ABAY_ARENA    => AbaiSemeyLentaAPI::getInstance(),
            API_SHOWMARKET          => ShowMarketAPI::getInstance(),
        ];
        return $arr[$this->vendor] ?? null;
    }

}
