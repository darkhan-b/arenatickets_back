<?php

namespace App\Models\DataStructures;

use App\Models\Types\TicketType;

class TicketsForGroupDTO {
    
    public $tickets;
    public $seats;
    public $prices;
    public string $type;
    
    public function __construct($type = null) {
        $this->tickets = [];
        $this->seats = [];
        $this->prices = [];
        $this->type = $type ?: TicketType::SEATS;
    }
    
}