<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Vacancy extends Model {

    use HasTranslations;
    use LogsActivity;
    use ActiveScopeTrait;

    protected $table = 'vacancies';

    public $translatable = [
        'title',
        'description',
    ];

    protected $fillable = [
        'title',
        'description',
        'active',
    ];

    protected $attributes = [
        'title' => '{}',
        'description' => '{}',
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
