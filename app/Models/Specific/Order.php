<?php

namespace App\Models\Specific;

use App\Mail\SendTicket;
use App\Models\API\TicketAgents\AgentIntegrationHelper;
use App\Models\General\User;
use App\Models\Helpers\SeatPicker;
use App\Models\Types\PaymentType;
use App\Traits\ClientTrait;
use App\Traits\VendorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model {

	use LogsActivity, SoftDeletes, VendorTrait, ClientTrait;

	protected $table = 'orders';

	protected $fillable = [
		'timetable_id',
		'user_id',
		'partner_id',
		'name',
		'email',
		'phone',
		'comment',
		'discount_rate',
		'price',
		'pay_id',
		'pay_sum',
		'pay_date',
		'pay_system',
		'pay_comment',
		'pay_url',
		'hide_price',
		'vendor',
		'vendor_id',
		'legal_entity_id',
		'hash',
		'ip',
		'paid',
		'sent',
		'reservation',
		'expiry_date',
		'available_for_manual_delete',
		'is_refundable',
		'show_to_organizer',
		'pay_system_imitated',
		'promocode',
		'country',
    	'position',
    	'company',
    	'participation',
		'promocode_discount_rate',
		'platform',
		'gcid'
	];

	protected $casts = [
		'hide_price'    => 'boolean',
		'paid'          => 'boolean',
		'sent'          => 'boolean',
		'is_refundable' => 'boolean',
		'show_to_organizer' => 'boolean',
	];

	protected $appends = ['ticketsLink'];

	/// *** Logging *** ///

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logFillable()
			->logOnlyDirty();
	}

	/// *** Scopes *** ///

	public function scopePaid($query) {
		return $query->where('paid', 1);
	}

	public function scopeNotInvitation($query) {
		return $query->where('pay_system', '<>', 'invitation');
	}

	/// *** Relations *** ///

	public function orderItems() {
		return $this->hasMany(OrderItem::class);
	}

	public function timetable() {
		return $this->belongsTo(Timetable::class);
	}

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function legalEntity() {
		return $this->belongsTo(LegalEntity::class, 'legal_entity_id');
	}

	/// *** Attributes *** ///

	public function getShowAttribute() {
		return $this->timetable ? $this->timetable->show : null;
	}

	public function getTicketsLinkAttribute() {
		if(!$this->paid) { return null; }
		return env('APP_URL').'/order/'.$this->id.'/'.$this->hash.'/pdf';
	}

	public function getPasskitLinkAttribute() {
		return env('APP_URL').'/passkit/'.$this->id.'/'.$this->hash;
	}

	public function getInitiatedByAttribute() {
		if(!$this->user) return 'user';
		$user = $this->user;
		$show = $this->show;
		if($show && $user->isOrganizerForShow($show->id)) {
			return 'organizer';
		}
		if($user->can('kassa')) {
			return 'kassir';
		}
		return 'user';
	}

	public function getExpiresAtAttribute() {
		$date = new \DateTime($this->created_at);
		return $date->modify('+ '.(ORDER_TIME_LIMIT - 1).' minutes'); // less by one 1 minute for jetpay tupnyak
	}

	public function getSecondsToExpiryAttribute() {
		$expiresAt = $this->expiresAt;
		$now = new \DateTime();
		return $expiresAt->getTimestamp() - $now->getTimestamp();
	}

	/// *** Custom *** ///

	public static function customDetails($id) {
		$obj = self::withTrashed()->with([
			'timetable',
			'timetable.show',
			'orderItems',
			'orderItems.section',
			'orderItems.pricegroup'
		])->find($id);
		$obj->append('initiatedBy');
		return $obj;
	}

	public function generateHash() {
		$hash = md5($this->id.'spaceduck'.$this->created_at);
		$this->hash = $hash;
		$this->save();
		return $hash;
	}

	public function isExpired() {
		$limit = date('Y-m-d H:i:s', strtotime('-'.ORDER_TIME_LIMIT.' minutes'));
		return $this->created_at < $limit;
	}

	public static function generateOrderFromRequest(Timetable $timetable, $user, $request, $partner = null) {

		if(!isset($request->cart) || !$request->cart || !is_array($request->cart) || count($request->cart) < 1) {
			return null;
		}

		$order = Order::create([
			'timetable_id'      => $timetable->id,
			'user_id'           => $user ? $user->id : null,
			'partner_id'        => $partner ? $partner->id : null,
			'name'              => $user ? $user->name : ($request->name ?? null),
			'phone'             => $user ? $user->phone : ($request->phone ?? null),
			'email'             => $user ? $user->email : ($request->email ?? null),
			'discount_rate'     => $timetable->discount,
			'vendor'            => $timetable->vendor,
			'legal_entity_id'   => $timetable->show?->legal_entity_id ?? null,
			'gcid'              => isset($request->cid) && $request->cid ? $request->cid : null,
			'expiry_date'       => date('Y-m-d H:i:s', strtotime('+'.(ORDER_TIME_LIMIT + 5).' minutes')),
			'platform'          => isset($request->source) && $request->source ? $request->source : DEFAULT_SOURCE,
			'reservation'     	=> $timetable->reservation_sale,
			'is_refundable'     => $request->is_refundable ?? 1,
			'ip'                => $request->ip(),
		]);

		$orderItems = $order->populateFromCart($request->cart, $timetable);

		if(!count($orderItems)) {
			$order->fullDelete();
			throw new \Exception('Выберите меньшее количество для оформления заказа');
		}

		$order->recountPrice();

		$order->generateHash();

		if($order->vendor) {
			$res = $order->vendorInit($timetable);
			if(!$res) {
				$order->fullDelete();
				return null;
			}
		}

		return $order;
	}

	public function populateFromCart($cart, $timetable) {
		$total_sum = 0;
		$orderItems = [];
		$cart = SeatPicker::pickSeatsForSectionsWithoutSeatSelection($cart, $timetable);
		DB::beginTransaction();
		$isVendor = (bool)$timetable->vendor;
		$discountRate = $timetable->discount;
		foreach($cart as $ticket) {
			$orderItem = null;
			$local_ticket = null;
			if(!$isVendor) {
				if(isset($ticket['ticket_id']) && $ticket['ticket_id']) {
					$local_ticket = Ticket::find($ticket['ticket_id']);
					if($local_ticket && $local_ticket->timetable_id != $this->timetable_id) {
						$local_ticket = null;
					}
				} else {
					if(isset($ticket['section_id']) && $ticket['section_id']) {
						$local_ticket = Ticket::available()
							->where('timetable_id', $this->timetable_id)
							->where('section_id', $ticket['section_id'])
							->first();
					}
					if(isset($ticket['pricegroup_id']) && $ticket['pricegroup_id']) {
						$local_ticket = Ticket::available()
							->where('timetable_id', $this->timetable_id)
							->where('pricegroup_id', $ticket['pricegroup_id'])
							->first();
					}
				}
			}

			$price = (!$isVendor && $local_ticket) ? $local_ticket->price : $ticket['price'];
			$cannotHaveLocalSeat = $isVendor && AgentIntegrationHelper::vendorHasOwnSeats($timetable->vendor);
			if($isVendor || ($local_ticket && !$local_ticket->blocked)) {
				$orderItem = OrderItem::create([
					'order_id'       => $this->id,
					'timetable_id'   => $this->timetable_id,
					'original_price' => $price,
					'price'          => round($price * (1 - $discountRate / 100), 2),
					'section_id'     => ($local_ticket && $local_ticket->section_id) ? $local_ticket->section_id : issetOrNull($ticket,'section_id'),
					'pricegroup_id'  => ($local_ticket && $local_ticket->pricegroup_id) ? $local_ticket->pricegroup_id : issetOrNull($ticket,'pricegroup_id'),
					'row'            => ($local_ticket && $local_ticket->row) ? $local_ticket->row : issetOrNull($ticket,'row'),
					'seat'           => ($local_ticket && $local_ticket->seat) ? $local_ticket->seat : issetOrNull($ticket,'seat'),
					'seat_id'        => $cannotHaveLocalSeat ? null : (($local_ticket && $local_ticket->seat_id) ? $local_ticket->seat_id : issetOrNull($ticket,'seat_id')),
					'vendor_seat_id' => $cannotHaveLocalSeat ? issetOrNull($ticket,'seat_id') : ($isVendor ? issetOrNull($ticket,'ticket_id') : null),
					'ticket_id'      => $local_ticket ? $local_ticket->id : null,
					'barcode'        => $local_ticket ? $local_ticket->barcode : null,
				]);
			}
			if(!$isVendor && $orderItem) {
				$orderItem->generateBarcode();
			}
			if($local_ticket) {
				$local_ticket->blocked = 1;
				$local_ticket->save();
			}
			$total_sum += $price;
			if($orderItem) $orderItems[] = $orderItem;
		}
		DB::commit();
		return $orderItems;
	}

	public function applyPromocode($promocode) {
		if(!$promocode || $this->pay_url) return; // we cannot change sum of order if payment page was already initiated
		$promocode_discount_rate = 0;
		$promocodeObj = Promocode::checkPromocode($promocode, $this->timetable_id);
		if($promocodeObj && $promocodeObj->canBeUsed($this)) {
			$promocode_discount_rate = $promocodeObj->discount;
		}
		$this->update([
			'promocode_discount_rate' => $promocode_discount_rate,
			'promocode'               => $promocode
		]);
	}

	public function vendorInit() {
		if(!$this->vendor) return null;
		$class = $this->getVendorClass();
		if($class) return $class::initiateOrder($this);
		return null;
	}

	public function prolong($minutes = 10) {
		if(!$this->expiry_date) return;
		$time = new \DateTime($this->expiry_date);
		$time->modify('+'.$minutes.' minutes');
		$this->expiry_date = $time->format('Y-m-d H:i:s');
		$this->available_for_manual_delete = 0;
		$this->save();
	}

	public function sendByEmail($email = NULL) {
		if(!env('SEND_EMAILS')) {
			return true;
		}
		if(!$email) {
			$email = $this->email;
		}
		try {
			Mail::to($email)->send(new SendTicket($this));
			$this->update(['sent' => 1]);
//			$this->sent = 1;
//			$this->save();
			Log::error('mail was sent successfully: order id'.$this->id);
			return true;
		} catch(\Exception $e) {
			Log::error($e);
			return false;
		}
	}

	public static function deleteOldOrders() {
		$orders = self::withoutGlobalScopes()
			->whereNull('deleted_at')
			->where("paid", 0)
			->where('created_at', '>', date('Y-m-d H:i:s',strtotime('-90 days')))
			->where('expiry_date', '<', date('Y-m-d H:i:s'))
			->get();
		foreach($orders as $order) {
			$order->delete('cron');
		}
	}

	public static function cleanTrashedOrders() {
		$emptyOrders = Order::withoutGlobalScopes()
			->onlyTrashed()
			->where('paid', 0)
			->whereNull('refunded_at')
			->whereNull('email')
			->where('created_at', '>', date('Y-m-d H:i:s',strtotime('-7 days')))
			->where('created_at', '<', date('Y-m-d H:i:s',strtotime('-1 days')))
			->get();
		foreach($emptyOrders as $order) {
			$order->fullDelete();
		}
		$filledVeryOldOrders = Order::withoutGlobalScopes()
			->onlyTrashed()
			->where('paid', 0)
			->whereNull('refunded_at')
			->where('created_at', '<', date('Y-m-d H:i:s',strtotime('-30 days')))
			->get();
		foreach($filledVeryOldOrders as $order) {
			$order->fullDelete();
		}
	}

	public static function deleteAllTrashed() { /// !!! DO NOT RUN IN PRODUCTION
		$orders = Order::withoutGlobalScopes()->onlyTrashed()->get();
		foreach($orders as $order) {
			$order->fullDelete();
		}
	}

	public static function blockAllSoldTickets($order = null, $days = 3) {
		return Ticket::whereIn('id', function ($query) use ($order, $days) {
			$query->select('ticket_id')
				->from('order_items')
				->join('orders', 'order_items.order_id', '=', 'orders.id')
				->where('orders.created_at', '>=', date('Y-m-d', strtotime('-'.$days.' days')).' 00:00:00')
				->whereNull('orders.deleted_at')
				->where('orders.paid', 1)
				->whereNotNull('ticket_id');
			if ($order) {
				$query->where('orders.id', $order->id);
			}
		})->where(function($q) {
			$q->where('blocked', 0)->orWhere('sold', 0);
		})->update([
			'blocked' => 1,
			'sold'    => 1
		]);
	}

	public function recover() {
		$items = $this->orderItems;
		$allTicketsAvailable = true;
		foreach($items as $item) {
			$ticket = $item->ticket;
			if(!$ticket) continue;
			if($ticket && ($ticket->blocked || $ticket->sold )) {
				$allTicketsAvailable = false;
			}
		}
		if(!$allTicketsAvailable) {
			return false;
		}
		self::blockAllSoldTickets($this);
		$this->restore();
//        $this->paid = 1;
//        $this->pay_date =
		$this->deleted_by = null;
		$this->save();
		return true;
	}


	public function successfullyPaid($amount, $send = true, $pay_date = null, $pay_id = null) {
		if($this->vendor) {
			$class = $this->getVendorClass();
			if($class) {
				$res = $class::payOrder($this);
				if(!$res) return false;
			} else {
				return false;
			}
		}
	
		$update_data = [
			'pay_sum' => $amount,
			'paid' => 1
		];
	
		if($pay_date) {
			$update_data['pay_date'] = $pay_date;
		}
	
		if(!$pay_date && !$this->pay_date) {
			$update_data['pay_date'] = date('Y-m-d H:i:s');
		}
	
		if($pay_id) {
			$update_data['pay_id'] = $pay_id;
		}
	
		$this->update($update_data);
	
		if(!$this->vendor) {
			$ticket_ids = $this->orderItems()
				->whereNotNull('ticket_id')
				->pluck('ticket_id')
				->toArray();
			if(count($ticket_ids) > 0) {
				Ticket::whereIn('id', $ticket_ids)
					->where('timetable_id',$this->timetable_id)
					->update(['sold' => 1]);
			}
		}
	
		if($send && !$this->sent) {
			$this->sendByEmail();
		}
	
		$this->updatePromocodeCount();
	
		return true;
	}
	

	public function soldAsInvitation() {
		$this->pay_system = PaymentType::INVITATION;
		$this->price = 0;
		$this->discount = $this->original_price;
		$this->internal_fee = 0;
		$this->external_fee = 0;
		$this->save();
		$this->successfullyPaid(0, false);
		return true;
	}

	public function soldAsForum() {
		$this->pay_system = PaymentType::FORUM;
		$this->price = 0;
		$this->discount = $this->original_price;
		$this->internal_fee = 0;
		$this->external_fee = 0;
		$this->save();
		$this->successfullyPaid(0, true);
		return true;
	}
	
	


	public function recountPrice() {
		$original_price = $this->orderItems()->sum('original_price');
		$discounted_price = $this->orderItems()->sum('price');
		$this->original_price = $original_price;
		$show = $this->show;
		$external_fee = $this->countFee('external', $discounted_price, $show);
		$internal_fee = $this->countFee('internal', $discounted_price, $show);
		$this->external_fee = $external_fee;
		$this->internal_fee = $internal_fee;
		$discount = $original_price - $discounted_price + round(($original_price * $this->promocode_discount_rate / 100), 2);
		$this->discount = $discount;
		$price_after_discount_promocode_and_fee = $original_price + $external_fee - $discount;
		$refundable_fee = $this->is_refundable ? round(($show->refundable_fee * $price_after_discount_promocode_and_fee / 100)) : 0;
		$this->refundable_fee = $refundable_fee;
		$this->price = $price_after_discount_promocode_and_fee + $refundable_fee;
		$this->save();
	}

	public function refund() {
		if(!$this->paid) return;
		$this->refunded_at = now();
		$timetableId = $this->timetable_id;
		$promocode = $this->promocode;
		$this->save();
		$this->delete();
		Promocode::updatePromocodeCount($timetableId, $promocode);
	}

	public function updatePromocodeCount() {
		Promocode::updatePromocodeCount($this->timetable_id, $this->promocode);
	}

	public static function findByIdAndHash($id, $hash) {
		return self::where([
			'id' => $id,
			'hash' => $hash
		])->first();
	}

	public function generateEmailConfirmationCode() {
		$code = rand(1000, 9999);
		$this->email_code = $code;
		$this->save();
		return $code;
	}

	private function countFee($type, $original_price, $show) {
		if(!$show) return 0;
		$value = $show->{$type.'_fee_value'};
		$type = $show->{$type.'_fee_type'};
		if($value <= 0) return 0;
		if($type === 'absolute') return round(($this->orderItems()->count() * $value), 2);
		if($type === 'percent') return round((($original_price * $value) / 100), 2);
		return 0;
	}

	public static function generateInvitationFromTickets($tickets, $hidePrice = false, $comment = null) {
		$user = Auth::user();
		if(!$user || !$tickets || count($tickets) < 1) return false;
		$timetable_id = $tickets[0]->timetable_id;
		$timetable = Timetable::find($timetable_id);
		$show = $timetable?->show;
		$order = Order::create([
			'timetable_id'      => $timetable_id,
			'user_id'           => $user->id,
			'name'              => $user->name,
			'phone'             => $user->phone,
			'email'             => $user->email,
			'comment'           => $comment,
			'pay_system'        => PaymentType::INVITATION,
			'legal_entity_id'   => $show?->legal_entity_id ?? null,
			'hide_price'        => $hidePrice,
			'expiry_date'       => date('Y-m-d H:i:s', strtotime('+'.(ORDER_TIME_LIMIT + 5).' minutes')),
			'platform'          => DEFAULT_SOURCE,
			'ip'                => Request::ip(),
		]);
		foreach($tickets as $ticket) {
			$oi = OrderItem::create([
				'order_id'          => $order->id,
				'timetable_id'      => $timetable_id,
				'price'             => $ticket->price,
				'original_price'    => $ticket->price,
				'section_id'        => $ticket->section_id,
				'pricegroup_id'     => $ticket->pricegroup_id,
				'row'               => $ticket->row,
				'seat'              => $ticket->seat,
				'seat_id'           => $ticket->seat_id,
				'ticket_id'         => $ticket->id,
			]);
			$oi->generateBarcode();
			$ticket->blocked = 1;
			$ticket->save();
		}
		$order->recountPrice();
		$order->generateHash();
		$order->soldAsInvitation();
		return $order;
	}

	public static function generateForumFromTickets($tickets, $hidePrice = false, $comment = null) {
		$user = Auth::user();
		if(!$user || !$tickets || count($tickets) < 1) return false;
		$timetable_id = $tickets[0]->timetable_id;
		$timetable = Timetable::find($timetable_id);
		$show = $timetable?->show;
		$order = Order::create([
			'timetable_id'      => $timetable_id,
			'user_id'           => $user->id,
			'name'              => $user->name,
			'phone'             => $user->phone,
			'email'             => $user->email,
			'comment'           => $comment,
			'pay_system'        => PaymentType::FORUM,
			'legal_entity_id'   => $show?->legal_entity_id ?? null,
			'hide_price'        => $hidePrice,
			'expiry_date'       => date('Y-m-d H:i:s', strtotime('+'.(ORDER_TIME_LIMIT + 5).' minutes')),
			'platform'          => DEFAULT_SOURCE,
			'ip'                => Request::ip(),
			'country'           => $user->country ?? null, 
			'position'          => $user->position ?? null,
			'company'           => $user->company ?? null, 
			'participation'     => $user->participation ?? null,
		]);
		
		foreach($tickets as $ticket) {
			$oi = OrderItem::create([
				'order_id'          => $order->id,
				'timetable_id'      => $timetable_id,
				'price'             => $ticket->price,
				'original_price'    => $ticket->price,
				'section_id'        => $ticket->section_id,
				'pricegroup_id'     => $ticket->pricegroup_id,
				'row'               => $ticket->row,
				'seat'              => $ticket->seat,
				'seat_id'           => $ticket->seat_id,
				'ticket_id'         => $ticket->id,
			]);
			$oi->generateBarcode();
			$ticket->blocked = 1;
			$ticket->save();
		}
		$order->recountPrice();
		$order->generateHash();
		$order->soldAsForum();
		return $order;
	}

	public function delete($deletedBy = 'manual') {
		if(!clientId()) setClientId($this->client_id);
		if($this->vendor) {
			$class = $this->getVendorClass();
			if($class) {
				try {
					if($this->paid) {
						$class::cancelPaidOrder($this);
					} else {
						$class::cancelUnpaidOrder($this);
					}
				} catch (\Exception $e) {}
			}
		} else {
			$ticket_ids = $this->orderItems()
				->whereNotNull('ticket_id')
				->pluck('ticket_id')
				->toArray();
			if(count($ticket_ids) > 0) {
				Ticket::whereIn('id', $ticket_ids)
					->where('timetable_id', $this->timetable_id)
					->whereDoesntHave('orderItems', function($q) {
						$q->whereHas('order', function ($q) {
							$q->paid()->where('id', '<>', $this->id);
						});
					})
					->update([
						'blocked' => 0,
						'sold'    => 0
					]);
			}
		}
		$this->deleted_by = $deletedBy;
		$this->save();
		return parent::delete();
	}

	public function fullDelete() {
		$this->orderItems()->delete();
		return $this->forceDelete();
	}


	public static function additionalSearchQuery($query, $search) {
		if(isset($search['show_id']) && $search['show_id']) {
			$query->whereHas('timetable', function($q) use($search){
				$q->whereHas('show', function($qt) use ($search) {
					$qt->where('id',$search['show_id'])
						->orWhereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search['show_id']) . '%']);
				});
			});
		}
		return $query;
	}


}
