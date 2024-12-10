<?php

namespace App\Models\General;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class News extends Model {

    use HasTranslations;
    use LogsActivity;
    use AnimatedMedia;
    use ActiveScopeTrait;
    use Sluggable;
    use SluggableScopeHelpers;

    protected $table = 'news';

    public $translatable = [
        'title', 'teaser', 'description'
    ];

    protected $fillable = [
        'title',
        'teaser',
        'description',
        'primary_media_id',
        'primary_media_ext',
        'date',
        'user_id',
        'slug',
        'active'
    ];

    protected $attributes = [
        'title'       => '{}',
        'teaser'      => '{}',
        'description' => '{}',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /// *** Media *** ///

    public $media_dir = 'news';

    public $store_primary_image = true;

    public $image_limit = 1;

    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 466,
            'height' => 292,
            'collections' => ['default']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 1200,
            'height' => 900,
            'collections' => ['default']
        ]
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///

    public function getLinkAttribute() {
        return localePath('news/'.$this->slug);
    }
    
    public function getCategoryAttribute() {
        return __('notification');
    }

    public function getImageAttribute() {
        return $this->imagePrimarySrc('thumb');
    }

    public function getShortTeaserAttribute() {
        return Str::limit(strip_tags($this->description), 300, '...');
    }

    public function getVeryShortTeaserAttribute() {
        return Str::limit(strip_tags($this->description), 100, '...');
    }

    /// *** Relations *** ///

    public function user() {
        return $this->hasMany(User::class);
    }

    /// *** Custom *** ///

}
