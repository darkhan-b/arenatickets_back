<?php

namespace App\Models\Specific;

use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ShowRoleActor extends Model {

    use HasTranslations;
    use LogsActivity;
    use SortableTrait;

    protected $table = 'show_roles_actors';

    public $translatable = [
        'custom_title', 
        'custom_subtitle', 
    ];

    protected $fillable = [
        'custom_title',
        'custom_subtitle',
        'role_id',
        'staff_id',
        'sort_order'
    ];

    protected $attributes = [
        'custom_title' => '{}',
        'custom_subtitle' => '{}',
    ];
    
    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
    
    /// *** Attributes *** ///



    /// *** Relations *** ///

    public function role() {
        return $this->belongsTo(ShowRole::class, 'role_id');
    }
    
    public function actor() {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
    
    /// *** Custom *** ///

}
