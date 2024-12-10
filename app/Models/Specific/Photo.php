<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Photo extends Model {

    use HasTranslations;
    use LogsActivity;
    use ActiveScopeTrait;
    use AnimatedMedia;

    protected $table = 'photos';

    public $translatable = [
        'title',
    ];

    protected $fillable = [
        'title',
        'active',
        'primary_media_id',
        'primary_media_ext'
    ];

    protected $attributes = [
        'title' => '{}',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $appends = ['teaser'];

    /// *** Media *** ///

    public $media_dir = 'photos';

    public $store_primary_image = true;

    public $image_limit = 1;
    
    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 150,
            'height' => 150,
            'collections' => ['default']
        ],
        'teaser' => [
            'type' => 'fit',
            'width' => 740,
            'height' => 540,
            'collections' => ['default']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 1600,
            'height' => 1200,
            'collections' => ['default']
        ]
    ];

    public function getTeaserAttribute() {
        return $this->imagePrimarySrc('teaser');
    }

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///
    
}
