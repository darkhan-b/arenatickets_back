<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Promocode extends Model {

    use LogsActivity;
    use ActiveScopeTrait;

    protected $table = 'promocodes';

    protected $fillable = [
        'promocode',
        'timetable_id',
        'discount',
        'times_used',
        'times_can_be_used',
        'applicable_to_price',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///

    public function setPromocodeAttribute($value) {
        $this->attributes['promocode'] = mb_strtolower($value);
    }

    /// *** Relations *** ///

    public function timetable() {
        return $this->belongsTo(Timetable::class);
    }

    /// *** Custom *** ///

    public static function checkPromocode(string $str, int $timetableId) : ?Promocode {
        $promocode = Promocode::where('timetable_id', $timetableId)
            ->where('promocode', mb_strtolower($str))
            ->first();
//        return $promocode ? $promocode->discount : 0;
        return $promocode;
    }

    public static function updatePromocodeCount(int $timetableId, string $str = null) {
        if($str) {
            $promo_condition = ['timetable_id' => $timetableId, 'promocode' => $str];
            Promocode::where($promo_condition)->update(['times_used' => Order::paid()->where($promo_condition)->count()]);
        }
    }

    public function canBeUsed(Order $order) {
        $orderItems = $order->orderItems;
        if($this->times_can_be_used && $this->times_can_be_used > 0) {
            $amountRequested = 1;
            $used = Order::where('promocode', $this->promocode)
                ->where('id', '<>', $order->id)
                ->where('timetable_id', $this->timetable_id)->count();
            if(($this->times_can_be_used - $used) < $amountRequested) {
                return false;
            }
        }
        $allOrderItemsFitPrice = true;
        $applicableToPrice = $this->applicable_to_price;
        if($applicableToPrice) {
            $allOrderItemsFitPrice = $orderItems->every(function ($item) use($applicableToPrice) {
                return $item->price == $applicableToPrice;
            });
        }
        return $allOrderItemsFitPrice;
    }

}
