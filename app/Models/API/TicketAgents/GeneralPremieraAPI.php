<?php

namespace App\Models\API\TicketAgents;

use App\Models\DataStructures\TicketsForGroupDTO;
use App\Models\Specific\Order;
use App\Models\Specific\Pricegroup;
use App\Models\Specific\Section;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use App\Models\Types\TicketType;
use App\Models\Types\TimetableType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeneralPremieraAPI implements TicketAPIInterface {

    protected static $url = '';
    protected static $port = '';
    protected static $service_id = '';
    protected static $platform = '';
    protected static $venue_id = null;
    protected static $venue_scheme_id = null;
    protected static $default_category_id = 1;
    protected static $ticket_design_id = 1;
    protected static $client_id = 1;
    protected static $timetablesPricegroups = []; // array of local $timetable_ids
    protected static $closedSectors = []; // should be of structure [$timetable_id => [$section_id, $section_id]], all local values
    protected static $enterSectors = []; // should be of structure [$timetable_id => [$section_id, $section_id]], all local values
    protected static $timetablesSyncLimit = null; // array of premiera timetable ids, if set, only those sessions will be synchronized
    protected static $xOffset = 0; // additional adjustment of seat x position, sometimes the row names are on top of seat, therefore we have to adjust
    protected static $yOffset = 0; // additional adjustment of seat y position

    public static function getInstance(): static {
        return new static();
    }

    public static function getHalls() {
        $data = static::request("&QueryCode=GetHalls&DateList=&Theatres=");
        return $data?->Theatres?->Theatre?->Halls ?? [];
    }

    public static function getHallPlan($timetable, $ourSectionId = null) { // ЗНАЧЕНИЕ СЕССИИ ОБЯЗАТЕЛЬНО
        $query = "&QueryCode=GetHallPlan&Theatres=&ListType=Row;X;Y;Width;Height;Place;Type;Fragment;Image;Status;Object;Background";
        $query .= "&Sessions=".$timetable->vendor_id;
        if ($ourSectionId) {
            $level = static::ourSectorToVendorSector($timetable, $ourSectionId);
            $query .= "&Levels=".$level;
        }
        $data = static::request($query);
        return $data->Session->Theatre ? $data->Session->Theatre->Hall->Levels : [];
    }


    public static function getTicketsForGroup(Timetable $timetable, $group_id) : TicketsForGroupDTO {
        $response = new TicketsForGroupDTO($timetable->type == TimetableType::PRICEGROUPS ? TicketType::PRICEGROUPS : TicketType::SEATS);
        if($response->type === TicketType::SEATS && static::sectionIsEnterSection($timetable, $group_id)) {
            $response->type = TicketType::ENTER;
        }
        $prices = [];
        if($response->type === TicketType::SEATS) {
            $section_id = $group_id;
            $arr = static::getHallPlan($timetable, $group_id);
            $pricegroups = static::getPrices($timetable);
            if(isset($arr->Level)) {
                foreach($arr->Level as $l) {
                    foreach($l->Places->Place as $place) {
//                        if ($attr['Status'] == 1 || (isset($enterData[$session]) && in_array($level,$enterData[$session]))) {
                        $row = getXmlAttribute($place,'Row');
                        $seat = getXmlAttribute($place,'Place');
                        $status = getXmlAttribute($place,'Status');
                        $fragment = getXmlAttribute($place,'Fragment');
                        $price = getXmlAttribute($place,'Type');
                        $id = getXmlAttribute($place,'ID');
                        $seat_id = $id;
                        $blocked = $sold = false;
                        $price = isset($pricegroups[$price]) && isset($pricegroups[$price]['price']) ? $pricegroups[$price]['price'] : 0;
                        $x = (int)getXmlAttribute($place,'X') + static::$xOffset;
                        $y = (int)getXmlAttribute($place,'Y') + static::$yOffset;
                        $ticket = null;
                        if ($price > 0 && $status == 1) {
                            $prices[] = $price;
                            $ticket = compact('id', 'row', 'seat', 'fragment', 'price', 'blocked', 'sold', 'section_id', 'seat_id');
                            $response->tickets[] = $ticket;
                        }
                        $response->seats[] = compact('id', 'row', 'seat', 'x', 'y', 'section_id', 'ticket');
                    }
                }
            }
        }

        if($response->type === TicketType::ENTER) {
            $price = static::findPriceForEnterSection($timetable, $group_id);
            for($i = 1; $i < 6; $i++) {
                $response->tickets[] = [
                    'id'            => $i,
                    'row'           => null,
                    'seat'          => null,
                    'seat_id'       => null,
                    'price'         => $price,
                    'section_id'    => $group_id,
                    'sold'          => false,
                    'blocked'       => false
                ];
            }
        }

        if($response->type === TicketType::PRICEGROUPS) {

        }

        $response->prices = array_values(array_unique($prices, SORT_NUMERIC));
//        $enterData = static::enterSectionsBySession();
//        if($timetable->type == TimetableType::PRICEGROUPS) {
//            $type = 'pricegroups';
//        }
//
//        if($type == 'enter') {
//            $arr = static::getTicketsForGroup($timetable, static::ourSectorToVendorSector($session,$level));
//            $pricegroup = Pricegroup::where('timetable_id',Timetable::where('vendor',static::$platform)->where('vendor_id',$session)->first()->id)
//                ->where('vendor',static::$platform)
//                ->where('description',Section::find($level)->name)
//                ->first();
//            $price = $pricegroup ? $pricegroup->price : 0;
//
//            if(isset($enterData[$session]) && in_array($level,$enterData[$session])) {
//                $arr = [];
//                for($i = 0; $i < 10; $i++) {
//                    $x = new \StdClass();
//                    $x->row = NULL;
//                    $x->fragment = 1;
//                    $x->seat = NULL;
//                    $x->status = 1;
//                    array_push($arr,$x);
//                }
//            }
//
//            foreach($arr as $place) {
//                $ticket = new \StdClass();
//                $ticket->row = $place->row;
//                $ticket->seat = $place->seat;
////                $ticket->price = $place->price;
//                $ticket->price = $price;
//                $ticket->status = $place->status;
//                $ticket->fragment = $place->fragment;
//                if (($ticket->status == 1 || (isset($enterData[$session]) && in_array($level,$enterData[$session]))) && $ticket->price > 0) {
//                    array_push($tickets,$ticket);
//                }
//            }
//
//        } else {

//        }

        return $response;
    }


    public static function getPrices(Timetable $timetable) {
        if(!$timetable || !$timetable->vendor_id) return [];
        $data = static::request("&QueryCode=GetSessionPrices&Levels=&Sessions=".$timetable->vendor_id);
        $arr = [];
        if (isset($data->PlacesTypes)) {
            foreach($data->PlacesTypes->PlaceType as  $pl) {
                $id = getXmlAttribute($pl,'ID');
                $price = getXmlAttribute($pl->Sum,'Price');
                $arr[$id] = [];
                $arr[$id]['price'] = $price / 100;
                $arr[$id]['name'] = (String)$pl->Name;
            }
        }
        return $arr;
    }

    public static function getEvents($session = null) {
        $query = "&QueryCode=GetMovies&Theatres=&Halls=&DateList=&ListType=PropertiesShow";
        if ($session) {
            $query .= '&Movies='.$session;
        }
        $data = static::request($query);
        return $data?->Movies ?? [];
    }

    public static function getTimetables($session = null) {
        $query = "&QueryCode=GetSessions&ListSort=CHLDM";
        if ($session) {
            $query .= '&Sessions='.$session;
        }
        $data = static::request($query);
        return $data?->Sessions ?? [];
    }

    public static function getListOfSectionsForEvent(Timetable $timetable) {
        $basic = static::getBasicHallsStructure();
        $levels = $basic[$timetable->vendor_scheme_id]->levels ?? [];
        return $levels;
    }

    public static function getBasicHallsStructure() {
        $halls = static::getHalls();
        $result = [];
        foreach($halls->Hall as $h) {
            $x = new \StdClass();
            $x->id = getXmlAttribute($h, 'ID');
            $x->name = (String)$h->Name;
            $levels = [];
            foreach($h->Levels->Level as $level) {
                $l = new \StdClass();
                $l->id = getXmlAttribute($level, 'ID');
                $l->name = (String)$level->Name;
                array_push($levels, $l);
            }
            $x->levels = $levels;
            $result[$x->id] = $x;
        }
        return $result;
    }

    public static function getHallsOfPlace(Timetable $timetable) {
        $our_sections = Section::forSale()
            ->where('venue_scheme_id', $timetable->venue_scheme_id)
            ->select('id','title')
            ->get();
        $arr = [];
        foreach($our_sections as $section) {
            $name = static::unifiedSectionName($section->title);
            $arr[$name] = $section->id;
        }
        return $arr;
    }


    public static function groupedCountTickets(Timetable $timetable) {
        $levels = static::getListOfSectionsForEvent($timetable);
//        dd($levels);
        $our_sections = static::getHallsOfPlace($timetable);
//        dd($our_sections);
        $apiSectionToLocalSection = [];
        foreach($levels as $l) {
            $n = static::unifiedSectionName($l->name);
            $apiSectionToLocalSection[$l->id] = isset($our_sections[$n]) ? $our_sections[$n] : 0;
        }
		$sectionsWithoutSeatsIds = self::getSectionWithoutSeatsIds($timetable);
//		return $levels;
//		return $apiSectionToLocalSection;
//        dd($apiSectionToLocalSection);
        $arr = [];
        $data = static::getHallPlan($timetable);
        if(isset($data->Level)) {
            foreach ($data->Level as $level) {
                $count = 0;
                $id = getXmlAttribute($level, 'ID');
                $localSectionId = $apiSectionToLocalSection[$id] ?? null;
                if(!$localSectionId) continue;
                if(static::isSectorClosed($timetable, $localSectionId)) continue;
                foreach ($level->Places->Place as $place) {
                    $status = getXmlAttribute($place, 'Status');
                    if($status == 1) { $count++; }
                }
                if (static::sectionIsEnterSection($timetable, $localSectionId, $sectionsWithoutSeatsIds) && $count == 0) {
                    $count = 10;
                }
                $arr[$localSectionId] = [
                    'cnt' => $count,
                    'section_id' => $localSectionId,
                    'section' => Section::where('id', $localSectionId)->select('id','title')->first()
                ];
            }
        }
        return $arr;
    }

    public static function synchronizeGeneral($withMinPrice = true) {
		setClientId(static::$client_id);
        static::synchronizeTimetables($withMinPrice);
    }

    public static function synchronizeTimetables($withMinPrice) {
        $timetables = static::getTimetables();
        if(!$timetables || !$timetables->Session) return;
        foreach($timetables->Session as $t) {
            $id = getXmlAttribute($t, 'ID');
            if(!static::$timetablesSyncLimit || in_array($id, static::$timetablesSyncLimit)) {
                static::syncTimetable($t, $withMinPrice);
            }
        }
    }

    public static function synchronizePriceGroups($timetable) {
        if($timetable) {
            $arr = [];
            $prices = static::getPrices($timetable);
            if($prices) {
                foreach($prices as $pricegroup_id => $price) {
                    $arr[] = $pricegroup_id;
                    $data = [
                        'timetable_id'  => $timetable->id,
                        'price'         => $price["price"],
                        'title'         => ['kz' => $price["name"], 'ru' => $price["name"], 'en' => $price["name"]],
                        'vendor_id'     => $pricegroup_id
                    ];
                    Pricegroup::updateOrCreate([
                        'vendor_id'     => $pricegroup_id,
                        'timetable_id'  => $timetable->id
                    ], $data);
                }
            }
            try {
                Pricegroup::where('timetable_id', $timetable->id)
                    ->whereNotIn('vendor_id', $arr)
                    ->delete();
            } catch (\Exception $e) {}
        }
    }

    public static function initiateOrder(Order $order) {
        $str = '';
        $timetable = $order->timetable;
        $items = $order->orderItems;
        foreach($items as $item) {
            $type = $item->ticketType;
            $vendor_section_id = static::ourSectorToVendorSector($timetable, $item->section_id);
//            $discount_id = (isset($t->discount_id) && $t->discount_id) ? ';d='.$t->discount_id : '';
            $discount_id = '';
            if($type === TicketType::SEATS) {
                $str .= '[l='.$vendor_section_id.';f='.$item->fragment.';r='.$item->row.';p='.$item->seat.$discount_id.']';
            }
            if($type === TicketType::ENTER) {
                $pricegroup = static::findPricegroupForEnterSection($timetable, $item->section_id);
                $str .= '[l='.$vendor_section_id.';f='.($item->fragment ?: 0).';t='.$pricegroup->vendor_id.']';
            }
            if($type === TicketType::PRICEGROUPS) {
                $pricegroup = Pricegroup::find($item->pricegroup_id);
                $str .= '[l='.$vendor_section_id.';f=0;t='.$pricegroup->vendor_id.$discount_id.']';
            }
        }
        $query = "&QueryCode=SaleReservation&Sessions=".$timetable->vendor_id."&ReservationID=".$order->id."&Places=".$str."&ReservationType=&Expect=";
        Log::error('premiera_reservation_query');
        Log::error($query);
        $data = static::request($query);
        Log::error('premiera_reservation_response');
        Log::error((array)$data);
        if(!$data || !$data->Reservation || !$data->Reservation->Sum) return false;
        $sum = (int)str_replace('руб 00коп', '', $data->Reservation->Sum);
        Log::error($sum);
        if($sum != $order->original_price) {
            Log::error('not equal');
            Log::error($sum);
            Log::error($order->original_price);
            return false;
        }
        return $data->Reservation;
    }


    public static function payOrder(Order $order) : bool {
        $items = $order->orderItems;
        if (count($items) < 1) {
            return false;
        }
        $first = $items[0];
        if (!$first->barcode) {
            $query = "&QueryCode=SaleApproved&ReservationID=".$order->id."&Expect=";
            $data = static::request($query);
//			Log::error('premiera_pay_response');
//			Log::error((array)$data);
            $count = 0;
            if (!isset($data->Reservation)) {
                Log::error(static::$platform.' order payment failed: order number: '.$order->id);
                return false;
            }
            foreach($data->Reservation->Places->Place as $place) {
                $barcode = getXmlAttribute($place, "Code");
                $items[$count]->update(['barcode' => $barcode]);
                $count++;
            }
            return true;
        } else {
            return true;
        }
    }


    public static function cancelUnpaidOrder(Order $order, $full = false) {
        $query = "&QueryCode=SaleCancel&ReservationID=".$order->id."&Expect=";
        $data = static::request($query);
        if($full) {
            return $data;
        }
        return $data->Reservation;
    }


    public static function cancelPaidOrder(Order $order) {
        $query = "&QueryCode=SalePayReturn&ReservationID=".$order->id."&Expect=";
        $data = static::request($query);
        return $data;
    }


    public static function getOrderStatus(Order $order, $full = false) {
        $query = "&QueryCode=GetSales&Theatres=&Halls=&Levels=&Movies=&DateList=&Sessions=&ListType=&ReservationID=".$order->id;
        $data = static::request($query);
        if($full == 'full') {
            return $data;
        }
        if(!$data->Sales || empty($data->Sales)) {
            return false;
        }
        if(date('Y-m-d H:i:s',strtotime($data->Sales->Reservation->Expired.':00')) > date('Y-m-d H:i:s')) {
            return true;
        }
        return false;
    }

    public static function ourSectorToVendorSector(Timetable $timetable, $our_id) {
        $section = Section::find($our_id);
        if (!$our_id || !$section) return null;
        $section_name = static::unifiedSectionName($section->title); // применимо только ко дворцу республики
        $levels = static::getListOfSectionsForEvent($timetable);
        foreach($levels as $l) {
            $lname = static::unifiedSectionName($l->name);
            if($lname == $section_name) {
                return $l->id;
            }
        }
        return null;
    }

    public static function isSectorClosed(Timetable $timetable, $section_id) { // our timetable id => [sector_ids]
        if(isset(static::$closedSectors[$timetable->id])) {
            return in_array($section_id, static::$closedSectors[$timetable->id]);
        }
        return false;
    }

    public static function sectionIsEnterSection(Timetable $timetable, $local_section_id, $sectionsWithoutSeatsIds = null) {
		if($sectionsWithoutSeatsIds === null) {
			$sectionsWithoutSeatsIds = static::getSectionWithoutSeatsIds($timetable);
		}
		if(in_array($local_section_id, $sectionsWithoutSeatsIds)) return true;
        $arr = static::$enterSectors;
        if(!isset($arr[$timetable->id])) return false;
        return in_array($local_section_id, $arr[$timetable->id]);
    }

	private static function getSectionWithoutSeatsIds(Timetable $timetable) {
		return Section::where('venue_scheme_id', $timetable->venue_scheme_id)->where('entrance', 1)->pluck('id')->toArray();
//		return Section::where('venue_scheme_id', $timetable->venue_scheme_id)->whereDoesntHave('seats')->pluck('id')->toArray();
	}

    public static function findPriceForEnterSection(Timetable $timetable, $section_id) {
        $pricegroup = static::findPricegroupForEnterSection($timetable, $section_id);
        return $pricegroup ? $pricegroup->price : 0;
    }

    public static function findPricegroupForEnterSection(Timetable $timetable, $section_id) {
        $section = Section::find($section_id);
        if(!$section) return 0;
        return Pricegroup::where('timetable_id',$timetable->id)
            ->where('title->ru', $section->title)
            ->first();
    }

    public static function syncShow($show) {
        $id = getXmlAttribute($show, 'ID');
        $name = (String)$show->Name;
        $duration = static::extractDuration((String)$show->Duration);
        $title = [
            'kz' => $name,
            'ru' => $name,
            'en' => $name
        ];
        $show = Show::firstOrNew([
            'vendor_id' => $id,
            'vendor'    => static::$platform,
            'client_id' => static::$client_id,
        ], [
            'venue_id'         => static::$venue_id,
            'category_id'      => static::$default_category_id,
            'ticket_design_id' => static::$ticket_design_id,
            'title'            => $title,
            'active'           => 0
        ]);
//        $show->title = $title;
        $show->duration = $duration;
        if(!$show->client_id) $show->client_id = static::$client_id;
        $show->save();
        if(!$show->categories()->exists()) {
            $show->categories()->sync([static::$default_category_id]);
        }
        return $show;
    }

    public static function syncTimetable($t, $withMinPrice) {
//        dd($t);
        $id = getXmlAttribute($t, 'ID');
        $type = TimetableType::SECTIONS;
        $api_hall_id = getXmlAttribute($t->Theatre->Hall,"ID");
        if(!$api_hall_id) {
            $type = TimetableType::PRICEGROUPS;
        }
        $date = \DateTime::createFromFormat('d.m.Y', $t->Date)->format('Y-m-d').' '.$t->Time.':00';
        $api_event_id = getXmlAttribute($t->Movie, 'ID');
        $api_show = static::getEvents($api_event_id);
        if(!$api_show) return;
        $show = static::syncShow($api_show->Movie);
        $our_event_id = $show->id;
        $timetable = Timetable::updateOrCreate([
            'vendor_id' => $id,
            'vendor'    => static::$platform,
			'client_id'	=> static::$client_id
        ],[
            'date'              => $date,
            'type'              => $type,
            'venue_id'          => static::$venue_id,
//            'venue_scheme_id'   => static::$venue_scheme_id,
            'vendor_scheme_id'  => $api_hall_id,
        ]);
//		if(!$timetable->client_id) $timetable->client_id = static::$client_id;
        if(!$timetable->show_id) {
            $timetable->show_id = $our_event_id;
            $timetable->save();
        }
        if(!$timetable->venue_scheme_id) {
            $timetable->venue_scheme_id = static::$venue_scheme_id;
            $timetable->save();
        }
        static::synchronizePriceGroups($timetable); // even for seats as well to record vendor pricegroup later
        if($withMinPrice) static::generatePriceString($api_event_id);
    }

    public static function request($query) {
		$url = 'http://'.static::$url.':'.static::$port.'/?ServiceID='.static::$service_id.$query.'&Encoding=UTF-8&Version=3';
		$request = Http::get($url);
		$response = $request->body();
		$xml = simplexml_load_string($response,'SimpleXMLElement', LIBXML_NOCDATA);
		return $xml->Data ?? null;
    }

//	public static function socket($query) {
//		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) {
//			$errorcode = socket_last_error();
//			$errormsg = socket_strerror($errorcode);
//			Log::error("Couldn't create socket: [$errorcode] $errormsg");
//			return null;
//		}
//
//		if(!socket_connect($sock , static::$url , static::$port)) {
//			$errorcode = socket_last_error();
//			$errormsg = socket_strerror($errorcode);
//			Log::error("Could not connect: [$errorcode] $errormsg");
//			return null;
//		}
//
//		socket_set_option($sock,SOL_SOCKET,SO_RCVTIMEO, [
//			'sec'=> 20,
//			'usec' => 0
//		]);
//
//		$service_id = static::$service_id;
//		$params = "&ServiceID=".$service_id."&Encoding=UTF-8&Version=3".$query;
//		$params = static::makeLength($params);
//
//		//Send the message to the server
//		if(!socket_send($sock, $params, strlen($params), 0)) {
//			$errorcode = socket_last_error();
//			$errormsg = socket_strerror($errorcode);
//			Log::error("Could not send data: [$errorcode] $errormsg");
//			return null;
//		}
//
//		//Now receive reply from server
//		if(socket_recv($sock,$buf,10000000,MSG_WAITALL) === FALSE) {
//			$errorcode = socket_last_error();
//			$errormsg = socket_strerror($errorcode);
//			Log::error("Could not receive data: [$errorcode] $errormsg");
//			return null;
//		}
//
//		$buf = substr($buf, 11);
//		$xml = simplexml_load_string($buf,'SimpleXMLElement', LIBXML_NOCDATA);
//		socket_close($sock);
//
//		return $xml ? $xml->Data : null;
//	}

    public static function extractDuration($string = null) {
        if(!$string) return null;
        $arr = explode('ч', $string);
        if(count($arr) == 2) {
            $mins = (str_replace('мин', '', trim($arr[1])));
            return (int)$arr[0] * 60 + (int)$mins;
        }
        return null;
    }

    public static function generatePriceString($event_id) {
        $show = Show::where([
            'vendor' 	=> static::$platform,
            'vendor_id' => $event_id
        ])->first();
        if($show) {
            $timetables = $show->timetables;
            $min = 0;
            $max = 0;
            foreach($timetables as $timetable) {
                $prices = static::getPrices($timetable);
                foreach($prices as $price) {
                    $max = max($price["price"],$max);
                    $min = min($price["price"],($min == 0 ? $max : $min));
                }
                if($min) {
                    $timetable->update(['min_cost_calculated' => $min]);
                }
            }
        }
    }

    public static function getSchedule() { // not used, just for info
        return static::request("&QueryCode=SheduleSessions&Movies=&Theatres=&Halls=&Levels=&ListSort=CHLDM&ListType=BusyPlaces&Expect=");
    }

    public static function getSessionPlacesCount($session) { // not used, just for info
        return static::request("&QueryCode=GetSessionPlaceCount&ListType=NoQuota&Expect=&Sessions=".$session);
    }

    public static function getReservationTypes() { // not used, just for info
        return static::request("&QueryCode=GetReservationTypes");
    }

    public static function getSessionPrice($session) { // not used, just for info
        return static::request("&QueryCode=GetSessionPrices&Sessions=".$session);
    }

    public static function printLevelsImage($hall) { // not used, just for info
        $data = static::request("&QueryCode=GetLevelsPlans&ListType=Image&Theatres=2&Halls=".$hall);
        $arr = (array)$data->Hall->Image;
        $img = $arr[0];
        $img = pack("H*", $img);
        $im = imagecreatefromstring($img);
        if ($im !== false) {
            header('Content-Type: image/jpeg');
            imagejpeg($im);
            imagedestroy($im);
        }
        else {
            echo 'An error occurred.';
        }
    }

    public static function unifiedSectionName($name) {
        $str = str_replace('Сектор ','', $name);
        $str = str_replace('(COPY)','', $str);
        $str = mb_strtolower($str);
        return $str;
    }

}
