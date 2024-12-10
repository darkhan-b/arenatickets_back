<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ClosedSection extends Model {

    use LogsActivity;

    protected $table = 'closed_sections'; 

    protected $fillable = [
        'timetable_id',
        'section_id',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function timetable() {
        return $this->belongsTo(Timetable::class, 'timetable_id');
    }

    public function section() {
        return $this->hasMany(Section::class, 'section_id');
    }
    
}
