<?php

namespace App\Models\Specific;

use App\Models\Types\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderItem extends Model {

    use LogsActivity;

    protected $table = 'order_items';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'timetable_id',
        'section_id',
        'pricegroup_id',
        'ticket_id',
        'row',
        'seat',
        'fragment',
        'seat_id',
        'vendor_seat_id',
        'vendor_index',
        'vendor_name',
        'price',
        'original_price',
        'barcode'
    ];

//    protected $hidden = ['barcode'];

//    protected $appends = ['fullSeatName'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function deletedOrder() {
        return $this->belongsTo(Order::class, 'order_id')->onlyTrashed();
    }

    public function orderWithTrashed() {
        return $this->belongsTo(Order::class, 'order_id')->withTrashed();
    }

    public function timetable() {
        return $this->belongsTo(Timetable::class);
    }

    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function pricegroup() {
        return $this->belongsTo(Pricegroup::class);
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function seat() {
        return $this->belongsTo(Seat::class);
    }

    /// *** Attributes *** ///

    public function getFullSeatNameAttribute() {
        if($this->pricegroup) return $this->pricegroup->title;
        $str = '';
        if($this->section) $str = $this->section->title;
        if($this->section && !$this->row && !$this->seat) {
            $str .= ', '.__('entrance');
        }
        if($this->row) {
            if($str != '') $str .= ', ';
            $str .= __('row').' '.$this->row;
        }
        if($this->seat) {
            if($str != '') $str .= ', ';
            $str .= __('seat').' '.$this->seat;
        }
        return $str;
    }

    public function getWeightedServiceFeeAttribute() {
        $order = $this->orderWithTrashed;
        if($order->external_fee <= 0 || $order->original_price <= 0) return 0;
        $percentage = $order->external_fee / $order->original_price;
        return round($percentage * $this->price);
    }

    public function getWeightedRefundableFeeAttribute() {
        $order = $this->orderWithTrashed;
        if($order->refundable_fee <= 0 || $order->original_price <= 0) return 0;
        $percentage = $order->refundable_fee / $order->original_price;
        return round($percentage * $this->price);
    }

	public function getWeightedPromocodeDiscountAttribute() {
		$order = $this->orderWithTrashed;
		if($order->promocode_discount_rate <= 0 || $order->original_price <= 0) return 0;
		return round(($this->original_price * $order->promocode_discount_rate / 100), 2);
	}

    public function getWeightedPriceAttribute() {
        return $this->price + $this->weightedServiceFee + $this->weightedRefundableFee - $this->weightedPromocodeDiscount;
    }

    public function getTicketTypeAttribute() {
        if($this->seat && $this->row) return TicketType::SEATS;
        if($this->section_id) return TicketType::ENTER;
        if($this->pricegroup_id) return TicketType::PRICEGROUPS;
        return TicketType::SEATS;
    }

    /// *** Custom *** ///

    public function delete() {
        if($this->ticket) {
            if(OrderItem::where('id', '<>', $this->id)->where('ticket_id', $this->ticket_id)->whereHas('order')->count() == 0) {
                $this->ticket->update([
                    'blocked' => 0,
                    'sold'    => 0
                ]);
            }
        }
        return parent::delete();
    }

    public function generateBarcode() {
        if(!$this->barcode) {
            $this->barcode = Str::padLeft($this->id, 8, '0').random_int(10000,99999);
            $this->save();
        }
    }


}
