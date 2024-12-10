<?php


namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class VenueScheme extends Model
{
    use HasTranslations;
    use LogsActivity;

    protected $table = 'venue_schemes';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'venue_id',
        'width',
        'height',
        'x',
        'y'
    ];

    public $translatable = [
        'title'
    ];

    protected $attributes = [
        'title' => '{"ru":"","kz":"","en":""}',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///

    public function getSeatsCountAttribute() {
        return Seat::whereIn('section_id', $this->sections()->pluck('id'))->count();
    }

    /// *** Relations *** ///

    public function venue() {
        return $this->belongsTo(Venue::class,'venue_id');
    }

    public function sections() {
        return $this->hasMany(Section::class,'venue_scheme_id');
    }

    public function timetables() {
        return $this->hasMany(Timetable::class, 'venue_scheme_id');
    }

    /// *** Custom *** ///

    public function customReplicate() {
        if(!$this->venue) return null;
        return $this->duplicate();
    }

    public function duplicate() {
        $scheme = $this->replicate();
        $scheme->title = [
            'ru' => $this->title.' (copy)',
            'en' => $this->title.' (copy)',
            'kz' => $this->title.' (copy)'
        ];
        $scheme->push();
        $sections = $this->sections;
        foreach($sections as $section) {
            $section->duplicate($scheme);
        }
        return $scheme;
    }

    public function delete() {
        if($this->timetables()->count() > 0) {
            throw ValidationException::withMessages(['show_message' => 'У рассадки есть сеансы']);
        }
        $sections = $this->sections;
        foreach($sections as $section) {
            $section->delete();
        }
        return parent::delete();
    }

}
