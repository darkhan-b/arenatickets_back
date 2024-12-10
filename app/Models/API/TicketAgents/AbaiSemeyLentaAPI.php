<?php

namespace App\Models\API\TicketAgents;

use App\Models\DataStructures\TicketsForGroupDTO;
use App\Models\General\DeveloperNotifier;
use App\Models\Specific\Order;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Pricegroup;
use App\Models\Specific\Section;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use App\Models\Types\TicketType;
use App\Models\Types\TimetableType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AbaiSemeyLentaAPI implements TicketAPIInterface {

    protected static $platform = API_SEMEY_ABAY_ARENA;
    protected static $venue_id = 69;
    protected static $venue_scheme_id = 83;
    protected static $default_category_id = 1;
    protected static $ticket_design_id = 1;
    protected static $vendorDateFormat = 'dmyHi';
//    protected static $timetablesPricegroups = []; // array of local $timetable_ids
    protected static $closedSectors = []; // should be of structure [$timetable_id => [$section_id, $section_id]], all local values
    protected static $enterSectors = []; // should be of structure [$timetable_id => [$section_id, $section_id]], all local values
    protected static $abaySectionsToLocal = [ // not all, only those that cannot be found by number
        12 => 635, // B4 left
        32 => 633, // B4 right
        19 => 612, // D4 left
        33 => 613,  // D4 right
    ];

    public static function getInstance(): static {
        return new static();
    }

    public static function getFunctions() {
        $client = new \SoapClient(env('ABAY_SEMEY_LENTA_URL').'/wsdl?wsdl');
        return $client->__getFunctions();
    }

    public static function synchronizeGeneral() {
        static::synchronizeTimetables();
    }

    public static function synchronizeTimetables() {
        $timetables = static::getTimetables();
        if(!$timetables) return;
        foreach($timetables as $t) {
            if($t->valid && $t->sale) {
                static::syncTimetable($t);
            }
        }
    }

    public static function syncShow($timetable) {
        $name = trim((String)$timetable->name);
        $duration = static::extractDuration($timetable);
        $title = [
            'kz' => $name,
            'ru' => $name,
            'en' => $name
        ];
        $show = Show::firstOrNew([
            'vendor_id' => $timetable->index,
            'vendor'    => static::$platform
        ], [
            'venue_id'         => static::$venue_id,
            'category_id'      => static::$default_category_id,
            'ticket_design_id' => static::$ticket_design_id,
            'title'            => $title,
            'active'           => 0
        ]);
        $show->duration = $duration;
        $show->save();
        if(!$show->categories()->exists()) {
            $show->categories()->sync([static::$default_category_id]);
        }
        return $show;
    }

    public static function syncTimetable($t) {
        $type = TimetableType::SECTIONS;
        $show = static::syncShow($t);
        $timetable = Timetable::updateOrCreate([
            'vendor_id' => $t->index,
            'vendor'    => static::$platform
        ],[
            'date'              => self::abayDateToOurDate($t->start),
            'type'              => $type,
            'venue_id'          => static::$venue_id,
            'venue_scheme_id'   => static::$venue_scheme_id,
            'vendor_scheme_id'  => $t->plan,
        ]);
        if(!$timetable->show_id) {
            $timetable->show_id = $show->id;
            $timetable->save();
        }
        static::synchronizePriceGroups($timetable); // even for seats as well to record vendor pricegroup later
    }

    public static function getTimetables() {
//        return self::soapRequest('getActiveSittings', '+');
        return self::soapRequest('getSittings','7', '5', '', '', '4', '', '', date(self::$vendorDateFormat));
    }

    public static function synchronizePriceGroups($timetable) {
        if(!$timetable) return;
        $prices = self::soapRequest('getSittingPrices', $timetable->vendor_id);
        if(!$prices) return;
        $min = 0;
        foreach($prices as $pricegroup) {
            $pricegroup_id = $pricegroup->zone;
            $name = $pricegroup->name;
            $price = $pricegroup->price;
            $arr[] = $pricegroup_id;
            $data = [
                'timetable_id'  => $timetable->id,
                'price'         => $price,
                'title'         => ['kz' => $name, 'ru' => $name, 'en' => $name],
                'vendor_id'     => $pricegroup_id
            ];
            Pricegroup::updateOrCreate([
                'vendor_id'     => $pricegroup_id,
                'timetable_id'  => $timetable->id
            ], $data);
            $min = min($price, ($min == 0 ? $price : $min));
            $timetable->update(['min_cost_calculated' => $min]);
        }
        try {
            Pricegroup::where('timetable_id', $timetable->id)
                ->whereNotIn('vendor_id', $arr)
                ->delete();
        } catch (\Exception $e) {}
    }

    public static function groupedCountTickets(Timetable $timetable) {
        $vendorSectorPrices = self::soapRequest('getSectorsPrices', $timetable->vendor_id);
        $ourSections = Section::where('venue_scheme_id', $timetable->venue_scheme_id)->select('title','id')->get();
        $arr = [];
        if(!$vendorSectorPrices) return $arr;
        foreach($vendorSectorPrices as $vsp) {
            $free_tickets = $vsp->free;
            $vendorSectorName = $vsp->sector_name;
            $vendorSectorId = $vsp->sector_id;
            $localSectionId = self::abaySectorIdToOurId($vendorSectorId, $vendorSectorName, $ourSections);
            if(!$localSectionId) continue;
            if(static::isSectorClosed($timetable, $localSectionId)) continue;
            $arr[$localSectionId] = [
                'cnt' => $free_tickets,
                'section_id' => $localSectionId,
                'section' => $ourSections->firstWhere('id', $localSectionId)
            ];
        }
        return $arr;
    }

    public static function getTicketsForGroup(Timetable $timetable, $group_id) : TicketsForGroupDTO {
        $section = null;
        $response = new TicketsForGroupDTO($timetable->type == TimetableType::PRICEGROUPS ? TicketType::PRICEGROUPS : TicketType::SEATS);
        if($response->type === TicketType::SEATS) {
            $section = Section::find($group_id);
            if(!$section) return $response;
        }
        if($response->type === TicketType::SEATS  && static::sectionIsEnterSection($timetable->id, $section)) $response->type = TicketType::ENTER;
        $vendorSectorId = null;
        if(in_array($response->type, [TicketType::SEATS, TicketType::ENTER])) {
            $vendorSectorId = self::ourSectorToAbaySectorId($section, $timetable->vendor_id);
            if(!$vendorSectorId) return $response;
            $prices = [];
        }
        if($response->type === TicketType::SEATS) {
            $tickets = self::soapRequest('getSeatsState', $timetable->vendor_id, $vendorSectorId);
            $ticketsKeys = [];
            if($tickets) {
                foreach($tickets as $ticket) {
                    if($ticket->state === 0) $ticketsKeys[] = $ticket->sid;
                }    
            }
            $plan = self::findPlanOfSector($vendorSectorId, $timetable->vendor_scheme_id);
            if(!$plan || !isset($plan->l->d->v->r)) return $response;
            $rows = $plan->l->d->v->r;
            $section_id = $section->id;
            foreach($rows as $rowitem) {
                $row = $rowitem->{'@attributes'}->i; 
                foreach($rowitem->p as $seat) {
                    $attrs = $seat->{'@attributes'};
                    if(isset($attrs->price)) {
                        $x = $attrs->x * 3.6;
                        $y = $attrs->y * 3.6;
                        $id = $seat_id = $attrs->s;
                        $seat = $attrs->i;
                        $fragment = 1;
                        $price = $attrs->price;
                        $blocked = $sold = false;
                        $ticket = null;
                        if(in_array($id, $ticketsKeys)) {
                            $prices[] = $price;
                            $ticket = compact('id','row', 'seat', 'fragment', 'price', 'blocked', 'sold', 'section_id', 'seat_id');
                            $response->tickets[] = $ticket;
                        }
                        $response->seats[] = compact('id', 'row', 'seat', 'x', 'y', 'section_id', 'ticket');   
                    }
                }
            }
        }
        if($response->type === TicketType::ENTER) {
            $vendorSectorPrices = self::soapRequest('getSectorsPrices', $timetable->vendor_id);
            $info = Arr::first($vendorSectorPrices, function ($item) use($vendorSectorId) {
                return $item->sector_id == $vendorSectorId;
            });
            if(!$info) return $response;
            $plan = self::findPlanOfSector($vendorSectorId, $timetable->vendor_scheme_id);
            if(!$plan || !isset($plan->r->p)) return $response;
            $seat_id = $plan->r->p->{'@attributes'}->s;
            $price = $info->price;
            $showTickets = min($info->free, 5);
            for($i = 1; $i < ($showTickets + 1); $i++) {
                $response->tickets[] = [
                    'id'            => $i,
                    'row'           => null,
                    'seat'          => null,
                    'seat_id'       => $seat_id,
//                    'seat_id'       => null,
                    'price'         => $price,
                    'section_id'    => $group_id,
                    'sold'          => false,
                    'blocked'       => false
                ];
            }
        }
        $response->prices = array_values(array_unique($prices, SORT_NUMERIC)); 
        return $response;
    }

    public static function initiateOrder(Order $order) {
        Log::info('abay: initiating order '.$order->id);
        $timetable = $order->timetable;
        $success = true; 
        $res = [];
        foreach($order->orderItems as $item) {
            $subres = self::soapRequest('toBookTickets', $timetable->vendor_id, $item->vendor_seat_id, $order->id);
            $subres = $subres[0];
            if($subres->state !== 0 || (int)$subres->price !== (int)$item->price) $success = false;
            if($subres->index) {
                $item->update([
                    'vendor_index' => $subres->index,
                    'vendor_name'  => $subres->name,
                ]); 
            }
            $res[] = $subres;
        }
        Log::info('success '.$success ? 'true' : 'false');
        Log::info($res);
        if(!$success) return false;
        return $res;
    }


    public static function payOrder(Order $order) : bool {
        Log::info('abay: starting sale of order '.$order->id);
        $bookRes = self::soapRequest('toSellTickets', $order->id);
        Log::info($bookRes);
        Log::info('abay: confirming order '.$order->id);
        $saleRes = self::soapRequest('toSellSuccess', $order->id);
        Log::info($saleRes);
        if(!$saleRes || ($saleRes['state'] ?? 0) != 2) return false;
        $success = true;
        $statusData = self::getOrderStatus($order);
        if($statusData) {
            foreach($statusData as $s) {
                if($s->barcode) {
                    $item = $order->orderItems()
                        ->where('vendor_name', $s->seat_name)
                        ->whereNull('barcode')
                        ->first();  // may be more than one for sections without seats
                    if($item) $item->update(['barcode' => $s->barcode]);
                } else {
                    $success = false;
                }
            }
        }
        if(!$success) DeveloperNotifier::error('abay error on sending pay order '.$order->id);
        return $success;
    }


    public static function cancelUnpaidOrder(Order $order, $full = false) {
        Log::info('cancelling abai semey unpaid order '.$order->id);
        $timetable = $order->timetable;
        $res = [];
        foreach($order->orderItems as $item) {
            if($item->vendor_index) {
                $subres = self::soapRequest('toUnBookTickets', $item->vendor_index, '', '', $order->id);    
            } else {
                $subres = self::soapRequest('toUnBookTickets', '', $item->vendor_seat_id, $timetable->vendor_id, $order->id);
            }
            $res[] = $subres[0];
        }
        Log::info($res);
        return $res;
    }


    public static function cancelPaidOrder(Order $order) {
        Log::info('abay: cancelling paid order '.$order->id);
        $items = $order->orderItems;
        $res = [];
        foreach($items as $item) {
            $subres = self::soapRequest('toReturnTicket', $order->id, $item->vendor_index);
            $r = $subres === 0 ? 'success' : 'failed';
            $res[] = $r;
            if($r === 'failed') DeveloperNotifier::error('abay error on cancelling paid order '.$order->id);
        }
        Log::info($res);
        return $res;
    }


    public static function getOrderStatus(Order $order, $full = false) {
        Log::info('abay: getting order details '.$order->id);
        $res = self::soapRequest('getOrderItems', $order->id);
        Log::info($res);
        return $res;
    }
    
    public static function extractDuration($timetable) {
        if(!$timetable) return null;
        $start = \DateTime::createFromFormat(self::$vendorDateFormat, $timetable->start)->getTimestamp();
        $end = \DateTime::createFromFormat(self::$vendorDateFormat, $timetable->end)->getTimestamp();
        $duration = round(($end - $start) / 60,2);
        return $duration > 0 ? $duration : null;
    }

    public static function abayDateToOurDate($dateString) {
        return \DateTime::createFromFormat(self::$vendorDateFormat, $dateString)->format('Y-m-d H:i:s');
    }

    public static function isSectorClosed(Timetable $timetable, $section_id) { // our timetable id => [sector_ids]
        if(isset(static::$closedSectors[$timetable->id])) {
            return in_array($section_id, static::$closedSectors[$timetable->id]);
        }
        return false;
    }
    
    public static function abaySectorIdToOurId($vendorSectorId, $vendorSectorName, $sectionsList) {
        if(isset(self::$abaySectionsToLocal[$vendorSectorId])) return self::$abaySectionsToLocal[$vendorSectorId];
        $section = $sectionsList->firstWhere('title', $vendorSectorName);
        if($section) return $section->id;
        return null;
    }
    
    public static function ourSectorToAbaySectorId(Section $ourSection, $vendorTimetableId) {
        $abaySectionsList = self::soapRequest('getSittingSectors', $vendorTimetableId);
        $localToAbay = array_flip(self::$abaySectionsToLocal);
        if(isset($localToAbay[$ourSection->id])) return $localToAbay[$ourSection->id];
        $key = array_search($ourSection->title, array_column($abaySectionsList, 'name'));
        if($key === false) return null;
        return $abaySectionsList[$key]->index;
    }
    
    public static function findPlanOfSector($vendorSectionId, $plan) {
        $xml = self::xmlRequest('plans/prepared/'.$plan.'.xml');
        $seats_sectors = [];
        $first_level = ['t', 's'];
        foreach($first_level as $key) {
            $data = $xml->prepared->{$key};
            if($key === 's') {
                foreach($data as $d) {
                    $seats_sectors[] = $d;    
                }
            }
            if($key === 't') {
                foreach($data as $d) {
                    foreach($d->s as $ds) {
                        $seats_sectors[] = $ds;
                    }
                }
            }
        }
        foreach($seats_sectors as $ss) {
            $id = $ss->{'@attributes'}->i ?? null;
            if((int)$id === $vendorSectionId) return $ss;
        }
        return null;
    }

    public static function sectionIsEnterSection($timetable_id, Section $section): bool {
        $arr = static::$enterSectors;
        if(isset($arr[$timetable_id]) && in_array($section->id, $arr[$timetable_id])) return true;
        return in_array($section->title, ['Фан-зона', 'Фан-Зона', 'VIP Фан-Зона']);
    }
    
    public static function soapRequest($functionName, ...$arguments) {
//        Log::info('abay: soap '.$functionName);
        try {
            $client = new \SoapClient(env('ABAY_SEMEY_LENTA_URL').'/wsdl?wsdl');
            $result = $client->{$functionName}(env('ABAY_SEMEY_LENTA_KEY'), ...$arguments);
            return $result;
        } catch (\SoapFault $e) {
            Log::error('abay: error on soap '.$functionName);
            Log::error($e->getMessage());
            return null;
        }
    }

    // http://82.200.215.102:1120/tickets/plans/prepared/3A1A222A363AEF7E4A5A626A.xml
    public static function xmlRequest($url) {
        try {
            $response = Http::accept('text/xml; charset=UTF8')->get(env('ABAY_SEMEY_LENTA_URL').'/'.$url);
            $data = $response->body();
            $xml = simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);
            $string = json_encode($xml);
            return json_decode($string);
        }   catch (\Exception $e) {
            Log::error('abay: error on xml '.$url);
            Log::error($e->getMessage());
            return null;
        }
    }


}