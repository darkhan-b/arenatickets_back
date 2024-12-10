<?php

namespace App\Models\API\TicketAgents;

class AgentIntegrationHelper {
    
    public static function vendorHasOwnSeats($vendor) {
        return in_array($vendor, [API_ALMATY_ARENA, API_DVOREC_RESPUBLIKI, API_SEMEY_ABAY_ARENA]);
    }
}