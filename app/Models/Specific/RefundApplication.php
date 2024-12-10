<?php

namespace App\Models\Specific;

use App\Mail\RefundApplicationApproved;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RefundApplication extends Model {

    use LogsActivity;

    protected $table = 'refund_applications';

    protected $fillable = [
        'order_id',
        'name',
        'phone',
        'email',
        'reason',
    ];
    
    protected $appends = ['refunded'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///
    
    public function getRefundedAttribute() {
        return $this->refunded_at ? true : false;
    }
    
    /// *** Relations *** ///

    public function order() {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    /// *** Custom *** ///

    public function refund() {
        $order = $this->order;
        if($order->refunded_at && !$this->refunded_at) {
            $this->refunded_at = $order->refunded_at;
            $this->save(); 
            return true;
        } 
        if($this->refunded_at || !$order || !$order->paid || $order->refunded_at) return false;
//        $timetable = $order->timetable;
//        if($timetable && !$timetable->availableForRefund) {
//            return false;
//        }
        $order->refund();
        $this->refunded_at = now();
        $this->save();
        Mail::send(new RefundApplicationApproved($this));
        return true;
    }

}
