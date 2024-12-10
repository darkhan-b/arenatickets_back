<?php

namespace App\Models\API\TicketAgents;

use App\Models\DataStructures\TicketsForGroupDTO;
use App\Models\Specific\Order;
use App\Models\Specific\Timetable;

interface TicketAPIInterface {
    
    public static function getInstance(): static;
    
    public static function synchronizeGeneral();
    
    public static function groupedCountTickets(Timetable $timetable);
    
    public static function getTicketsForGroup(Timetable $timetable, $group_id) : TicketsForGroupDTO;
    
    public static function initiateOrder(Order $order);
    
    public static function payOrder(Order $order) : bool;
    
    public static function getOrderStatus(Order $order);
    
    public static function cancelUnpaidOrder(Order $order);
    
    public static function cancelPaidOrder(Order $order);
    
}