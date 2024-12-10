<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Partner extends Model {

    use HasTranslations;
    use LogsActivity;
    use ActiveScopeTrait;
    use SortableTrait;
    use AnimatedMedia;

    protected $table = 'partners';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
        'active',
        'sort_order',
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

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Media *** ///

    public $media_dir = 'partners';

    public $store_primary_image = true;

    public $image_limit = 1;

    public $conversions = [
        'thumb' => [
            'type' => 'resize',
            'width' => 200,
            'height' => 100,
            'collections' => ['default']
        ],
        'teaser' => [
            'type' => 'resize',
            'width' => 260,
            'height' => 130,
            'collections' => ['default']
        ]
    ];

    public function getTeaserAttribute() {
        return $this->imagePrimarySrc('teaser');
    }


}
