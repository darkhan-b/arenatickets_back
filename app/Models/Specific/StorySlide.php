<?php

namespace App\Models\Specific;

use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class StorySlide extends Model {

    use HasTranslations;
    use SortableTrait;
    use AnimatedMedia;

    protected $table = 'stories_slides';

    public $translatable = [
        'button_title'
    ];

    protected $fillable = [
        'button_title',
        'story_category_id',
        'sort_order',
        'url',
        'primary_media_id',
        'primary_media_ext'
    ];

    protected $attributes = [
        'button_title' => '{}',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $appends = ['teaser'];

    /// *** Media *** ///

    public $media_dir = 'stories';

    public $store_primary_image = true;

    public $image_limit = 1;

    public $conversions = [
        'thumb' => [
            'type' => 'resize',
            'width' => 100,
            'height' => 200,
            'collections' => ['wallpaper']
        ],
        'teaser' => [
            'type' => 'resize',
            'width' => 660,
            'height' => 980,
            'collections' => ['wallpaper']
        ]
    ];

    public function getTeaserAttribute() {
        return env('APP_URL').$this->imagePrimarySrc('teaser');
    }

    /// *** Relations *** ///

    public function category() {
        return $this->belongsTo(StoryCategory::class, 'story_category_id');
    }


}
