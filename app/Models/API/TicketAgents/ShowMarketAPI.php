<?php

namespace App\Models\API\TicketAgents;

use App\Models\DataStructures\TicketsForGroupDTO;
use App\Models\General\DeveloperNotifier;
use App\Models\Specific\Order;
use App\Models\Specific\Pricegroup;
use App\Models\Specific\Seat;
use App\Models\Specific\Section;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use App\Models\Specific\VenueScheme;
use App\Models\Types\TicketType;
use App\Models\Types\TimetableType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShowMarketAPI implements TicketAPIInterface {


    public static function getInstance(): static {
        return new static();
    }

    public static function synchronizeGeneral() {
        $shows = static::request('events_list');
        foreach($shows as $show) {
            $id = $show['id'] ?? null;
            if(!$id) continue;
//            if(!$id || !in_array($id, [200,213,201])) continue;
            $condition = [
                'vendor_id' => $id,
                'vendor'    => API_SHOWMARKET
            ];
            if(Show::where($condition)->exists()) {
                $timetable = Timetable::where($condition)->first();
                $timetable->update(['date' => $show['event_date']]);
            } else {
                $venueSchemeId = static::showMarketPlaceToVenue($show['place']);
                $venueId = null;
                if($venueSchemeId) {
                    $venueScheme = VenueScheme::find($venueSchemeId);
                    if($venueScheme) $venueId = $venueScheme->venue_id;
                }
                $ourShow = Show::create(array_merge($condition, [
                    'title'         => array_fill_keys(['ru', 'kz', 'en'], $show['name']),
                    'description'   => array_fill_keys(['ru', 'kz', 'en'], $show['about']),
                    'venue_id'      => $venueId,
                    'slug'          => $show['event_alias'],
                    'active'        => 0
                ]));
                $ourShow->categories()->sync([1]);
                $timetableType = static::getShowType($id);
                $timetable = Timetable::create(array_merge($condition, [
                    'show_id'           => $ourShow->id,
                    'date'              => $show['event_date'],
                    'venue_id'          => $venueId,
                    'venue_scheme_id'   => $venueSchemeId,
                    'type'              => $timetableType
                ]));
            }
            if($timetable->type === TimetableType::PRICEGROUPS) {
                static::syncPricegroups($timetable);
            }
        }
    }

    public static function groupedCountTickets(Timetable $timetable) {
        $arr = [];
        $ourSections = Section::where('venue_scheme_id', $timetable->venue_scheme_id)->select('title','id')->get();
        $details = static::getShowDetails($timetable->vendor_id);
        if($timetable->type === TimetableType::SECTIONS) {
            foreach($details['sectors'] as $sector) {
                $localSectionId = self::showMarketSectorToOurSectorId($sector['alias'], $sector['name'], $ourSections);
                $arr[$localSectionId] = [
                    'cnt' => $sector['count_seats_for_sale'],
                    'section_id' => $localSectionId,
//                    'section' => Section::where('id', $localSectionId)->select('id','title')->first()
                    'section' => $ourSections->firstWhere('id', $localSectionId)
                ];        
            }
        }
        if($timetable->type === TimetableType::PRICEGROUPS) {
            $pgs = $timetable->pricegroups()
                ->get()
                ->keyBy('vendor_id')
                ->toArray();
            foreach($details['ticket_types'] as $vendorPricegroup) {
                $localPricegroup = $pgs[$vendorPricegroup['id']] ?? null;
                if(!$localPricegroup) continue;
                $arr[] = [
                    'cnt'           => $vendorPricegroup['count_tickets_for_sale'],
                    'pricegroup'    => $localPricegroup,
                    'pricegroup_id' => $localPricegroup['id']
                ];
            }
        }
        
        return $arr;
    }

    public static function getTicketsForGroup(Timetable $timetable, $group_id): TicketsForGroupDTO {
        $response = new TicketsForGroupDTO($timetable->type == TimetableType::PRICEGROUPS ? TicketType::PRICEGROUPS : TicketType::SEATS);
        $sector_alias = static::ourSectorToShowmarketSectorId($group_id, $timetable);
        if(!$sector_alias) return $response;
        $res = static::request('get_sector/'.$timetable->vendor_id.'/'.$sector_alias);
//        Log::error($res);
        if(isset($res['price'])) { // means it is enter section
            $response->type = TicketType::ENTER;
            for($i = 1; $i < 6; $i++) {
                $response->tickets[] = [
                    'id'            => $i,
                    'row'           => null,
                    'seat'          => null,
                    'seat_id'       => null,
                    'price'         => $res['price'],
                    'section_id'    => $group_id,
                    'sold'          => false,
                    'blocked'       => false
                ];
            }
        } else {
            $prices = [];
            $tickets = [];
            foreach($res as $r) {
                if($r['for_sale']) {
                    $prices[] = $r['price'];
//                    $ticket = compact('id', 'row', 'seat', 'fragment', 'price', 'blocked', 'sold', 'section_id', 'seat_id');
                    $ticket = [
                        'id'            => $r['id'],
                        'row'           => $r['row'],
                        'seat'          => $r['name'],
                        'price'         => $r['price'],
                        'fragment'      => 1,
                        'section_id'    => $group_id
                    ];
                    $response->tickets[] = $ticket;
                    $tickets[$r['row'].'-'.$r['name']] = $ticket;
                }
            }
            $seats = Seat::where('section_id', $group_id)->get();
            $seats->map(function($item) use($tickets) {
                $key = $item->row.'-'.$item->seat;
                if(isset($tickets[$key])) {
                    $t = $tickets[$key];
                    $t['seat_id'] = $item->id;
                    $item->ticket = $t;  
                } 
                return $item;
            });
            $response->seats = $seats;
            $response->prices = array_values(array_unique($prices));
        }
        
        return $response;
    }

    public static function initiateOrder(Order $order) {
        $timetable = $order->timetable;
        $items = $order->orderItems;
        $booking_id = 0;
        foreach($items as $item) {
            $type = $item->ticketType;
            $amount = 1;
            $seat_id = 0;
            $type_id = 0;
            $sector_alias = 0;
            if($type === TicketType::SEATS) $seat_id = $item->vendor_seat_id;
            if($type === TicketType::PRICEGROUPS && $item->pricegroup) $type_id = $item->pricegroup->vendor_id;
            if($type === TicketType::ENTER) $sector_alias = static::ourSectorToShowmarketSectorId($item->section_id, $timetable);
            $url = 'booking/'.$timetable->vendor_id.'/'.$booking_id.'/'.$amount.'/'.$seat_id.'/'.$type_id.'/'.$sector_alias;
            $result = static::request($url);
            Log::error('showmarket booking');
            Log::error('url:'. $url);
            Log::error($result);
            $booking_id = $result['id'] ?? 0;
            $vendorOrderItemId = $result['ticket_id'] ?? null;
            if($vendorOrderItemId) $item->update(['vendor_index' => $vendorOrderItemId]);
        }
        $order->update(['vendor_id' => $booking_id]);
        return $booking_id;
    }

    public static function payOrder(Order $order): bool {
        $res = static::request('order/'.$order->vendor_id.'/'.$order->id);
        Log::error('showmarket confirm');
        Log::error($res);
        if(!isset($res['order_id']) || !$res['order_id']) return false;
//        if(($res['order_id'] ?? null) != $order->id) return false;
        $items = $order->orderItems()->get()->keyBy('vendor_index')->all();
        foreach($res['list'] as $r) {
            $items[$r['id']]->update(['barcode' => $r['number']]); 
        }
        return true;
    }

    public static function getOrderStatus(Order $order) {
        return static::request('status/'.$order->vendor_id);
    }

    public static function cancelUnpaidOrder(Order $order) {
        return static::request('delete/'.$order->vendor_id);
    }

    public static function cancelPaidOrder(Order $order) {
        $items = $order->orderItems;
        $success = true;
        foreach($items as $item) {
            $description = 'Возврат билета клиентом';
            $url = 'return/'.$item->barcode.'/'.$description;
            Log::error('showmarket '.$url);
            $res = static::request($url);
            Log::error($res);
            if(($res['Status'] ?? null) != 'ok') {
                $success = false;
                DeveloperNotifier::error('showmarket paid order '.$order->id.' cancellation failed');
            }
        }
        return $success;
    }
    
    public static function syncPricegroups($timetable) {
        $details = static::getShowDetails($timetable->vendor_id);
        $pricegroupIds = [];
        foreach($details['ticket_types'] as $tt) {
            $pricegroupIds[] = $tt['id'];
            $data = [
                'timetable_id'  => $timetable->id,
                'price'         => $tt["price"],
                'title'         => array_fill_keys(['ru', 'kz', 'en'], $tt['title']),
                'vendor_id'     => $tt['id']
            ];
            Pricegroup::updateOrCreate([
                'vendor_id'     => $tt['id'],
                'timetable_id'  => $timetable->id
            ], $data);
        }
        try {
            Pricegroup::where('timetable_id', $timetable->id)
                ->whereNotIn('vendor_id', $pricegroupIds)
                ->delete();
        } catch (\Exception $e) {}
    }
    
    public static function getShowType($showId) {
        $details = static::getShowDetails($showId);
        if(isset($details['sectors']) && count($details['sectors']) > 1) {
            return TimetableType::SECTIONS;
        }
        if(isset($details['ticket_types']) && count($details['ticket_types']) > 1) {
            return TimetableType::PRICEGROUPS;
        }
        return TimetableType::SECTIONS;
    }
    
    public static function getShowDetails($showId) {
        return static::request('get_event/'.$showId);
    }
    
    public static function showMarketSectorToOurSectorId($vendorSectorId, $vendorSectorName, $sectionsList) {
        $arr = static::sectorsDictionary();
        if(isset($arr[$vendorSectorId])) return $arr[$vendorSectorId];
        $section = $sectionsList->firstWhere('title', $vendorSectorName);
        if($section) return $section->id;
        return null;
    }

    public static function ourSectorToShowmarketSectorId($ourSectionId, $timetable) {
        $localToShowmarket = array_flip(static::sectorsDictionary());
        if(isset($localToShowmarket[$ourSectionId])) return $localToShowmarket[$ourSectionId];
        $vendorSectionsList = (static::getShowDetails($timetable->vendor_id))['sectors'];
        $section = Section::find($ourSectionId);
        $key = array_search($section->title, array_column($vendorSectionsList, 'name'));
        if($key === false) return null;
        return $vendorSectionsList[$key]['alias'];
    }

    public static function showMarketPlaceToVenue($placeName) {
        $arr = [
            'Дворец спорта им. Балуана Шолака'  => 109,
            'Рок-клуб Жесть'                    => 106,
            'Дворец единоборств «Jekpe-Jek»'    => 110,
            'г.Уральск "Ледовый Дворец Спорта"' => 108,
            'Конгресс-центр'                    => 89,
            'Ледовый Дворец города Шымкент'     => 111
        ];
        return $arr[$placeName] ?? null;
    }
    public static function request($url) {
        $res = Http::get(env('SHOWMARKET_URL').$url);
        return $res->json();
    }
    
    public static function sectorsDictionary() {
        return [
//            'A4'            => null,
//            'A3'            => null,
//            'A2'            => null,
//            'C13'           => null,
//            'C12'           => null,
//            'C11'           => null,
//            'C10'           => null,
//            'C9'            => null,
//            'C8'            => null,
//            'C7'            => null,
//            'B10'           => null,
//            'B9'            => null,
//            'B8'            => null,
//            'B7'            => null,
//            'B6'            => null,
//            'B5'            => null,
//            'section2'      => null,
//            'section3'      => null,
//            'section6'      => null,
//            'section7'      => null,
//            'section8'      => null,
//            'section1'      => null,
//            'section4'      => null,
//            'section5'      => null,
//            'section9'      => null,
//            'parterp'       => null,
//            'parter'        => null,
//            'amfiteatrp'    => null,
//            'amfiteatr'     => null,
//            'balkon'        => null,
//            'DRA'           => null,
//            'DRB'           => null,
//            'DRV'           => null,
//            'DRG'           => null,
//            'DRD'           => null,
//            'DRE'           => null,
//            'DRZh'          => null,
//            'DRI'           => null,
//            'DRK'           => null,
//            'DRL'           => null,
//            'DRM'           => null,
//            'DRN'           => null,
//            'ds1'           => 938,
//            'ds2'           => null,
//            'ds3'           => null,
//            'ds4'           => null,
//            'ds5'           => null,
//            'ds6'           => null,
//            'ds7'           => null,
//            'ds8'           => null,
//            'ds9'           => null,
//            'ds10'          => null,
//            'ds11'          => null,
//            'ds12'          => null,
//            'ds13'          => 950,
//            'ds14'          => 951,
//            'ds15'          => null,
//            'ds16'          => null,
//            'ds17'          => null,
//            '61260'         => 950,
//            '65067'         => null,
//            '76013'         => null,
//            'cbsec1'        => null,
//            'cbsec2'        => null,
//            'cbsec3'        => null,
//            'cbsec4'        => null,
//            'cbsec5'        => null,
//            'cbsec6'        => null,
//            'cbsec7'        => null,
//            'cbsec8'        => null,
//            'cbsec9'        => null,
//            'cbsecfan'      => null,
//            'cbdance'       => null,
//            'shfan'         => null,
//            'sha'           => null,
//            'shb'           => null,
//            'shc'           => null,
//            'shd'           => null,
//            'she'           => null,
//            'shf'           => null,
//            'ta-parter'     => null,
//            'ta-amfi'       => null,
//            'ta-balkon'     => null,
//            'ta-balkon2'    => null,
        ];
    }
}