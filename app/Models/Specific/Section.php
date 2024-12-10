<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Section extends Model {

    use HasTranslations, LogsActivity;

    protected $table = 'sections';

    protected $fillable = [
        'title',
        'note',
        'venue_scheme_id',
        'entrance',
        'svg',
        'for_sale',
        'show_title'
    ];

    public $translatable = [
        'title',
        'note',
    ];

    protected $attributes = [
        'title' => '{"ru":"","kz":"","en":""}',
        'note' => '{"ru":"","kz":"","en":""}',
    ];

    protected $casts = [
        'title'         => 'json',
        'note'          => 'json',
        'svg'           => 'json',
        'entrance'      => 'boolean',
        'for_sale'      => 'boolean',
        'show_title'    => 'boolean',
    ];

    /// *** Attributes *** ///

    public function getSeatsCountAttribute() {
        return $this->seats()->count();
    }

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Scopes *** ///

    public function scopeForSale($query) {
        return $query->where('for_sale', 1);
    }

    /// *** Relations *** ///

    public function scheme() {
        return $this->belongsTo(VenueScheme::class, 'venue_scheme_id');
    }

    public function seats() {
        return $this->hasMany(Seat::class);
    }

    public function tickets() {
        return $this->hasMany(Ticket::class, 'section_id');
    }

    /// *** Custom *** ///

    public function duplicate(VenueScheme $scheme) {
        $section = $this->replicate();
        $section->venue_scheme_id = $scheme->id;
        $section->push();
        $seats = $this->seats;
        foreach($seats as $seat) {
            $new_seat = $seat->replicate();
            $new_seat->section_id = $section->id;
            $new_seat->push();
        }
    }

    public static function customDetails($id) {
        $section = static::findOrFail($id);
        $section->seats;
        $section->scheme;
        $section->scheme->venue;
        return $section;
    }

    public function delete() {
        $this->seats()->delete();
        return parent::delete();
    }

}
