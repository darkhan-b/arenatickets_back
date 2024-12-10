<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ShowTermPoint extends Model {

    use HasTranslations, LogsActivity;

    protected $table = 'show_terms_points';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
        'show_term_id',
    ];

    protected $attributes = [
        'title' => '{}',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function showTerm() {
        return $this->belongsTo(ShowTerm::class, 'show_term_id');
    }

}
