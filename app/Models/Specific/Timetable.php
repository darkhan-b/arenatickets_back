<?php

namespace App\Models\Specific;

use App\Models\API\TicketAgents\DvorecRespublikiAPI;
use App\Models\API\TicketAgents\TicketAPIInterface;
use App\Models\Types\PaymentType;
use App\Models\Types\TicketType;
use App\Models\Types\TimetableType;
use App\Traits\ActiveScopeTrait;
use App\Traits\ClientTrait;
use App\Traits\VendorTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Timetable extends Model {

    use HasTranslations, LogsActivity, ActiveScopeTrait, VendorTrait, ClientTrait;

    protected $table = 'timetables';

    protected $fillable = [
        'show_id',
        'date',
        'show_till',
        'sell_till',
        'type',
        'note',
        'venue_id',
        'venue_scheme_id',
        'vendor',
        'vendor_id',
        'vendor_scheme_id',
        'min_cost_calculated',
        'meta_data',
        'sale_starts_soon',
        'temporary_blocked',
        'cancelled',
        'postponed',
        'sold_out',
        'reservation_sale',
        'active',
        'discount',
    ];

    public $translatable = [
        'note'
    ];

    protected $casts = [
        'active'            => 'boolean',
        'sale_starts_soon'  => 'boolean',
        'temporary_blocked' => 'boolean',
        'cancelled'         => 'boolean',
        'postponed'         => 'boolean',
        'sold_out'          => 'boolean',
        'reservation_sale'  => 'boolean',
        'meta_data'         => 'json',
    ];

    protected $attributes = [
        'note' => '{"ru":"","kz":"","en":""}',
    ];

    protected $appends = ['availableForRefund'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    protected static function boot() {

        parent::boot();

        self::created(function($timetable) {
            $timetable->generateUUID();
        });
    }

    /// *** Scopes *** ///

    public function scopeFuture($query) {
        return $query->active()->where(function($q) {
            $q->where(function($q) {
                $q->where('date', '>=', date('Y-m-d H:i:s', strtotime('-2 hours')))
                    ->whereNull('show_till');
            })->orWhere(function($q) {
                $q->where('show_till', '>=', date('Y-m-d H:i:s'))
                    ->whereNotNull('show_till');
            });
        });
    }

    public function scopePassed($query) {
        return $query->where(function($q) {
            $q->where('date', '<=', date('Y-m-d H:i:s'));
        });
    }

    public function scopeVisibleTill($query) {
        return $query->where(function($q) {
            $q->where('show_till', '>=', date('Y-m-d H:i:s'))
                ->orWhereNull('show_till');
        });
    }

    public function scopeLocal($query) {
        return $query->whereNull('vendor');
    }

    public function scopeUserHasAccessToData($query, $user) {
        return $query->whereHas('show', function ($query) use($user) {
            $query->userHasAccessToData($user);
        });
    }

    /// *** Attributes *** ///

//    public function getLinkAttribute() {
//        if(!$this->show) return localePath('afisha');
//        return localePath('event/'.$this->show->slug);
//    }

    public function getDatePlaceStringAttribute() {
        $str = $this->dateString;
        $plString = $this->placeString;
        if($plString) {
            $str .= ', '.$plString;
        }
        return $str;
    }

    public function getPassedAttribute() {
        return $this->date <= date('Y-m-d H:i:s');
    }

    public function getDateStringAttribute() {
        return date('d.m, H:i', strtotime($this->date));
    }

    public function getSecondsToStartAttribute() {
        $startTime = Carbon::parse($this->date);
        $now = Carbon::now();
        return $now->diffInSeconds($startTime);
    }

    public function getHoursToStartAttribute() {
        return floor($this->secondsToStart / (60 * 60));
    }

    public function getAvailableForRefundAttribute() {
        return $this->hoursToStart >= REFUND_APPLICATION_LIMIT;
    }

    public function getPlaceStringAttribute() {
        if($this->venue) {
            return $this->venue->title;
        }
        return '';
    }

    public function getMinCostAttribute() {
        if($this->min_cost_calculated) return $this->min_cost_calculated;
        return Cache::remember('minCost-'.$this->id,3600, function() {
            return $this->tickets()->available()->min('price');
        });
    }

//    public function getPurchaseLinkAttribute() {
//        return '/widget#/'.$this->show_id.'/'.$this->id;
//    }

    public function getPlacesLeftAttribute() {
        return Cache::remember('placesLeft-'.$this->id,1800, function() {
            return $this->tickets()->available()->count();
        });
    }

    public function getTotalTicketsAttribute() {
        return $this->tickets()->count();
    }

    public function getSoldTicketsAttribute() {
        return OrderItem::whereHas('order', function($q) {
            $q->where('timetable_id', $this->id)->paid();
        })->count();
    }

    public function getInvitationSalesAttribute() {
        return OrderItem::whereHas('order', function($q) {
            $q->where('timetable_id', $this->id)
                ->where('pay_system', 'invitation')
                ->paid();
        })->count();
    }

    public function getReturnedTicketsAttribute() {
        return OrderItem::whereHas('deletedOrder', function($q) {
            $q->where('timetable_id', $this->id)
                ->whereNotNull('refunded_at');
        })->count();
    }

    public function getAvailableTicketsAttribute() {
        return $this->tickets()->available()->count();
    }

    public function getRevenueAttribute() {
        return $this->orders()->paid()->sum('original_price')
            - $this->orders()->paid()->sum('discount');
    }

    public function getTicketsScannedAttribute() {
        return $this->scans()->groupBy('barcode')->select('barcode', DB::raw('count(*) as total'))->get()->count();
    }

    public function getTotalScansAttribute() {
        return $this->scans()->count();
    }

    public function getGroupFieldAttribute() {
        $group_field = 'section_id';
        if($this->type == 'pricegroups') {
            $group_field = 'pricegroup_id';
        }
        return $group_field;
    }

    public function getSalesFinishedAttribute() {
        if(!$this->sell_till) return false;
        return $this->sell_till < date('Y-m-d H:i:s');
    }

    public function setMetaDataAttribute($value) {
        $arr = [];
        if($value) {
            foreach($value as $key => $v) {
                if($v) $arr[$key] = $v;
            }
        }
        $this->attributes['meta_data'] = count($arr) ? json_encode($arr) : null;
    }

    public function getHasPromocodesAttribute() {
        return $this->promocodes()->active()->count() > 0;
    }

    public function generateUUID() {
        $this->uuid = Str::uuid();
        $this->save();
    }

    /// *** Relations *** ///

    public function show() {
        return $this->belongsTo(Show::class);
    }

    public function venue() {
        return $this->belongsTo(Venue::class);
    }

    public function scheme() {
        return $this->belongsTo(VenueScheme::class, 'venue_scheme_id');
    }

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    public function pricegroups() {
        return $this->hasMany(Pricegroup::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function scans() {
        return $this->hasMany(Scan::class, 'timetable_id');
    }

    public function closedSections() {
        return $this->hasMany(ClosedSection::class, 'timetable_id');
    }

    public function sectionsWithoutSeatSelections() {
        return $this->hasMany(SectionWithoutSeatSelection::class, 'timetable_id');
    }

    public function promocodes() {
        return $this->hasMany(Promocode::class, 'timetable_id');
    }

    /// *** Custom *** ///

    public static function getMonths() {
        $res = self::active()->orderBy('date','asc')
            ->where('date', '>', date('Y-m-d H:i:s', strtotime('-4 hours')))
            ->get()
            ->groupBy(function($val) {
                return Carbon::parse($val->date)->format('Y-m');
            })->toArray();
        return array_keys($res);
    }

    public function groupedCountTickets($show_closed = false) {
        if($this->vendor) {
            $class = $this->getVendorClass();
            if($class) return $this->closedSectionsRemover($class::groupedCountTickets($this), $show_closed);
        }
        $q = $this->tickets()
            ->available()
            ->selectRaw('COUNT(id) as cnt, '.$this->group_field);
        if($this->type === TimetableType::SECTIONS) {
            $q->with('section:id,title');
        }
        $data = $q->groupBy($this->group_field)
            ->get()
            ->keyBy($this->group_field)
            ->toArray();
		if($this->type === TimetableType::SECTIONS) {
			$data = $this->closedSectionsRemover($data, $show_closed);
		}
        if($this->type === TimetableType::PRICEGROUPS) {
            $pgs = $this->pricegroups()
				->with('sections:id,title')
                ->get()
                ->keyBy('id')
                ->toArray();
            $data = array_map(function($item) use($pgs) {
                $item['pricegroup'] = isset($pgs[$item['pricegroup_id']]) ? $pgs[$item['pricegroup_id']] : null;
                return $item;
            }, $data);
        }
        return $data;
    }

    public function closedSectionsRemover($data, $show_closed = false) {
        if($show_closed) return $data;
        $section_ids = $this->closedSections()->pluck('section_id')->toArray();
        return array_diff_key($data, array_flip($section_ids));
    }

    public function getTicketsForGroup($id = null, $withblocked = false, $invitationSeparate = false) {
        if($this->vendor) {
            $class = $this->getVendorClass();
            if($class) return $class::getTicketsForGroup($this, $id);
        }
        $q = $this->tickets()
            ->select('id','section_id', 'row', 'seat', 'seat_id', 'price', 'blocked', 'sold');
        if($id) $q->where($this->group_field, $id);
        if(!$withblocked) {
            $q->available();
        }
        $tickets = $q->get();
        $type = TicketType::ENTER;
        if($this->type == TimetableType::PRICEGROUPS) {
            $type = TimetableType::PRICEGROUPS;
        }
        if($this->type == TimetableType::SECTIONS && count($tickets) > 0 && $tickets[0]->seat_id) {
            $type = TicketType::SEATS;
            if($invitationSeparate) {
                $soldWithInvitation = OrderItem::where('section_id', $id)->whereHas('order', function($q) {
                    $q->paid()
                        ->where('timetable_id', $this->id)
                        ->where('pay_system', PaymentType::INVITATION);
                })->pluck('ticket_id')->toArray();
                $tickets->map(function($item) use($soldWithInvitation) {
                    $item->soldAsInvitation = $item->sold && in_array($item->id, $soldWithInvitation);
                    return $item;
                });
            }
        }
        $prices = Ticket::arrayPrices($tickets->pluck('price')->toArray());
        $sectionIds = $id ? [$id] : Section::where(['venue_scheme_id' => $this->venue_scheme_id])->pluck('id')->toArray();
        $seats = $type == TimetableType::PRICEGROUPS ? [] : Seat::whereIn('section_id', $sectionIds)->get();
        if($type == TicketType::SEATS) {
            $ticks = $tickets->keyBy('seat_id')->toArray();
            $seats->map(function($item) use($ticks) {
                if(isset($ticks[$item->id])) {
                    $item->ticket = $ticks[$item->id];
                }
                return $item;
            });
        }
		if($type === TicketType::SEATS) {
			$withoutSeatSelection = SectionWithoutSeatSelection::where(['timetable_id' => $this->id, 'section_id' => $id])->first();
			if($withoutSeatSelection) $type = TicketType::ENTER;
		}
        return [
            'type'       => $type,
            'prices'     => $prices,
            'seats'      => $seats,
            'tickets'    => $tickets,
        ];
    }

    public static function customValidationOnUpdate($request, $obj = null) {
        if($obj && $obj->venue_scheme_id && $request->venue_scheme_id != $obj->venue_scheme_id) { // venue scheme changed
            if($obj->totalTickets > 0) {
                throw ValidationException::withMessages(['show_message' => 'На текущей рассадке уже заведены билеты']);
            }
        }
    }

    public static function additionalSearchQuery($query, $search) {
        $query->whereHas('show', function($q) {
            $q->doesNotRequireApproval();
        });
        return $query;
    }

    public static function customPrepareModel($data = []) {
        $showId = (int)($data['show_id'] ?? null);
        $show = Show::find($showId);
        $timetable = new Timetable();
        if($show) {
            $timetable->venue_id = $show->venue_id;
            $timetable->show_id = $showId;
        }
        return $timetable;
    }

    public static function customSelectOptions($data = []) {
        $showId = (int)($data['show_id'] ?? null);
        $show = Show::find($showId);
        $venue = null;
        $schemes = [];
        if($show) {
            $venue = Venue::find($show->venue_id);
            $schemes = $venue->schemes()->get();
        }
        return [
            'venue_id'          => $venue ? [$venue] : [],
            'venue_scheme_id'   => $schemes,
        ];
    }

    public static function customDetails($id) {
        $obj = self::with('show')->find($id);
        $obj->append(['dateString', 'ticketsScanned', 'totalTickets', 'revenue', 'availableTickets', 'returnedTickets', 'soldTickets']);
        return $obj;
    }

    public function delete() {
        if($this->orders()->paid()->count() < 1) {
            $orders = $this->orders()->withTrashed()->get();
            foreach($orders as $order) {
                $res = $order->fullDelete();
            }
            $this->tickets()->delete();
            $this->pricegroups()->delete();
            return parent::delete();
        }
        return false;
    }

}
