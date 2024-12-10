<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Youtube extends Model {

    use HasTranslations;
    use LogsActivity;
    use ActiveScopeTrait;

    protected $table = 'youtubes';

    public $translatable = [
        'title', 'teaser'
    ];

    protected $fillable = [
        'title',
        'teaser',
        'url',
        'active',
    ];

    protected $attributes = [
        'title' => '{}',
        'teaser' => '{}',
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

    /// *** Relations *** ///
    
}
