<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model {

    use LogsActivity;

    protected $table = 'clients';

    protected $fillable = [
        'title',
        'status'
    ];

    protected $attributes = [
        'status'       => 'active',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

}
