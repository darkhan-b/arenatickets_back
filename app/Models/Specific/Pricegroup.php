<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Pricegroup extends Model {

    use HasTranslations;
    use LogsActivity;

    protected $table = 'pricegroups';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
        'vendor_id',
        'timetable_id',
        'price',
        'color',
        'sort_order'
    ];

    protected $attributes = [
        'title' => '{}',
    ];

    protected $appends = ['amount'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///

    public function getAmountAttribute() {
        return $this->tickets_count ? $this->tickets_count : null;
    }

    /// *** Relations *** ///

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    public function sections() {
        return $this->belongsToMany(Section::class, 'pricegroup_section', 'pricegroup_id', 'section_id');
    }

    /// *** Custom *** ///

    public function setTicketsAmount($amount, $onlyadd = false) {
        $newTickets = [];
        $this->tickets()->update([
            'price' => $this->price
        ]);
        $already_exists_amount = $onlyadd ? 0 : $this->tickets()->count();
        if($amount >= $already_exists_amount) {
            $amount = $amount - $already_exists_amount;
            for($i = 0; $i < $amount; $i++) {
                $newTickets[] = Ticket::create([
                    'timetable_id'  => $this->timetable_id,
                    'price'         => $this->price,
                    'pricegroup_id' => $this->id,
                ]);
            }
            return $newTickets;
        }
        if($amount < $already_exists_amount) {
            $amount_to_delete = $already_exists_amount - $amount;
            Ticket::where('timetable_id',$this->timetable_id)
                ->where('pricegroup_id',$this->id)
                ->available()
                ->take($amount_to_delete)
                ->delete();
            return $newTickets;
        }
        return $newTickets;
    }


    public function delete() {
        if($this->tickets()->unavailable()->count() > 0) {
            return false;
        }
        $this->tickets()->delete();
        return parent::delete();
    }

}


