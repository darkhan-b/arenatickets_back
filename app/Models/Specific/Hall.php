<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Hall extends Model {

    use HasTranslations;
    use LogsActivity;

    protected $table = 'halls';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title', 
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

    public function shows() {
        return $this->hasMany(Show::class);
    }


}
