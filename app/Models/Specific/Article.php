<?php

namespace App\Models\Specific;

use App\Models\General\User;
use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Article extends Model {

    use HasTranslations;
    use LogsActivity;
    use AnimatedMedia;
    use ActiveScopeTrait;
    use Sluggable;
    use SluggableScopeHelpers;

    protected $table = 'articles';

    public $translatable = [
        'title', 
        'teaser', 
        'description'
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

    protected $appends = ['teaserimg'];

    /// *** Media *** ///

    public $media_dir = 'articles';

    public $store_primary_image = true;

    public $image_limit = 1;

    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 466,
            'height' => 292,
            'collections' => ['default']
        ],
        'teaser' => [
            'type' => 'fit',
            'width' => 1120,
            'height' => 580,
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
        return localePath('article/'.$this->slug);
    }

    public function getTeaserimgAttribute() {
        return $this->imagePrimarySrc('teaser');
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
