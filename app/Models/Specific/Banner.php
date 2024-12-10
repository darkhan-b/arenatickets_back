<?php


namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use LogsActivity;
    use SortableTrait;
    use ActiveScopeTrait;
    use AnimatedMedia;
    use HasTranslations;

    protected $table = 'banners'; 

    protected $fillable = [
        'url',
        'active',
        'position',
        'sort_order',
        'primary_media_id',
        'primary_media_ext',
    ];

    protected $attributes = [
        'url' => '{}',
    ];

    public $translatable = [
        'url',
    ];

    /// *** Media *** ///

    public $media_dir = 'banners';

    public $store_primary_image = true;

    public $image_limit = 1;
    
    public $conversions = [];

    public $topConversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 400,
            'height' => 240,
            'collections' => ['default','wallpaper']
        ],
//        'teaser' => [
//            'type' => 'fit',
//            'width' => 400,
//            'height' => 240,
//            'collections' => ['default','wallpaper']
//        ],
        'mobile' => [
            'type' => 'resize',
            'width' => 670,
            'height' => 200,
            'collections' => ['banner']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 2220,
            'height' => 240,
            'collections' => ['default','wallpaper']
        ]
    ];
    
    public $bottomConversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 400,
            'height' => 240,
            'collections' => ['default','wallpaper']
        ],
//        'teaser' => [
//            'type' => 'fit',
//            'width' => 400,
//            'height' => 240,
//            'collections' => ['default','wallpaper']
//        ],
        'mobile' => [
            'type' => 'resize',
            'width' => 670,
            'height' => 360,
            'collections' => ['banner']
        ],
        'main' => [
            'type' => 'fit',
            'width' => 1080,
            'height' => 460,
            'collections' => ['default','wallpaper']
        ]
    ];

    protected $casts = [
        'active'    => 'boolean',
    ];

    protected $appends = ['teaser', 'main', 'mobile'];
    
    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Attributes *** ///

    public function getTeaserAttribute() {
        return env('APP_URL').$this->imagePrimarySrc('thumb');
    }
    
    public function getMainAttribute() {
        $src = $this->imagePrimarySrc('main');
        return $src ? env('APP_URL').$src : '';
    }

    public function getMobileAttribute() {
        $src = $this->imageSrc('mobile', 'banner');
        if($src) {
            return env('APP_URL').$this->imageSrc('mobile', 'banner');
        }
        return $this->main;
    }

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
        if($data['position'] === 'top') $obj->conversions = $obj->topConversions;
        if($data['position'] === 'bottom') $obj->conversions = $obj->bottomConversions;
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        $this->update($data);
        if($data['position'] === 'top') $this->conversions = $this->topConversions;
        if($data['position'] === 'bottom') $this->conversions = $this->bottomConversions;
        return $this;
    }

}
