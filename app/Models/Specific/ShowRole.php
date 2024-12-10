<?php

namespace App\Models\Specific;

use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ShowRole extends Model {

    use HasTranslations;
    use LogsActivity;
    use SortableTrait;

    protected $table = 'show_roles';

    public $translatable = [
        'title', 
    ];

    protected $fillable = [
        'title',
        'show_id',
        'sort_order'
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
    
    /// *** Attributes *** ///



    /// *** Relations *** ///

    public function show() {
        return $this->belongsTo(Show::class, 'show_id');
    }
    
    public function actors() {
        return $this->hasMany(ShowRoleActor::class, 'role_id');
    }
    
    /// *** Custom *** ///

}
