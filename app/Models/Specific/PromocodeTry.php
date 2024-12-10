<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class PromocodeTry extends Model {

    protected $table = 'promocode_tries';

    protected $fillable = [
        'ip',
    ];

    const UPDATED_AT = null;

    public static function clean() {
        self::where('created_at', '<', date('Y-m-d H:i:s', strtotime('yesterday')))->delete();
    }
    
}
