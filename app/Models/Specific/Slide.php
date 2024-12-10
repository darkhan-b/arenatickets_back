<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Slide extends Model {

    use HasTranslations;
    use LogsActivity;
    use ActiveScopeTrait;
    use AnimatedMedia;
    use SortableTrait;

    protected $table = 'slides';

    public $translatable = [
        'title',
        'subtitle',
        'url',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'url',
        'show_id',
        'active',
        'sort_order',
        'primary_media_id',
        'primary_media_ext'
    ];

    protected $attributes = [
        'title'         => '{}',
        'subtitle'      => '{}',
        'url'           => '{}',
        'sort_order'    => 1
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $appends = ['teaser', 'slide', 'mobile'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Media *** ///

    public $media_dir = 'slides';

    public $store_primary_image = true;

    public $image_limit = 1;

    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 150,
            'height' => 100,
            'collections' => ['default','wallpaper','banner']
        ],
        'mobile' => [
            'type' => 'fit',
            'width' => 800,
            'height' => 540,
            'collections' => ['banner']
        ],
        'main' => [
            'type' => 'fit',
            'width' => 1860,
            'height' => 720,
            'collections' => ['default','wallpaper']
        ]
    ];

    /// *** Attributes *** ///

    public function getTeaserAttribute() {
        if($this->show && $this->show->teaser) return $this->show->teaser;
        return env('APP_URL').$this->imagePrimarySrc('thumb');
    }

    public function getMainAttribute() {
        if($this->show && $this->show->main) return $this->show->main;
        return env('APP_URL').$this->imagePrimarySrc('main');
    }

    public function getSlideAttribute() {
        if($this->imagePrimarySrc('main')) {
            return env('APP_URL').$this->imagePrimarySrc('main'); // main for slide is slide for show
        }
        if($this->show && $this->show->slide) return $this->show->slide;
        return null;
    }

    public function getMobileAttribute() {
        if($this->show && $this->show->mobile) return $this->show->mobile;
        $src = $this->imageSrc('mobile', 'banner');
        if($src) {
            return env('APP_URL').$this->imageSrc('mobile', 'banner');
        }
        return $this->main;
    }

    /// *** Relations *** ///

    public function show() {
        return $this->belongsTo(Show::class);
    }

}
