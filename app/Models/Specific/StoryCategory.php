<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class StoryCategory extends Model {

    use HasTranslations;
    use ActiveScopeTrait;
    use SortableTrait;
    use AnimatedMedia;

    protected $table = 'stories_categories';

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
        'sort_order' => 1
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
            'width' => 116,
            'height' => 116,
            'collections' => ['wallpaper']
        ],
    ];

    public function getTeaserAttribute() {
        return env('APP_URL').$this->imagePrimarySrc('thumb');
    }

    /// *** Relations *** ///

    public function slides() {
        return $this->hasMany(StorySlide::class, 'story_category_id')->sorted();
    }

}
