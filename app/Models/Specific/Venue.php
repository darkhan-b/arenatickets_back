<?php


namespace App\Models\Specific;

use App\Scopes\ClientScope;
use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\ClientTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Venue extends Model
{
    use HasTranslations, LogsActivity, ActiveScopeTrait, AnimatedMedia, Sluggable, SluggableScopeHelpers, ClientTrait;

    protected $table = 'venues';

    protected $fillable = [
        'title',
        'description',
        'address',
        'schedule',
        'phone',
        'website',
        'venue_category_id',
        'city_id',
        'x_coord',
        'y_coord',
        'primary_media_id',
        'primary_media_ext',
        'slug',
        'active'
    ];

    public $translatable = [
        'title',
        'address',
        'schedule',
        'description'
    ];

    protected $attributes = [
        'title' => '{"ru":"","kz":"","en":""}',
        'address' => '{"ru":"","kz":"","en":""}',
        'schedule' => '{"ru":"","kz":"","en":""}',
        'description' => '{"ru":"","kz":"","en":""}',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /// *** Media *** ///

    public $media_dir = 'venues';

    public $store_primary_image = true;

    public $image_limit = 10;

    public $conversions = [
        'thumb' => [
            'type' => 'resize',
            'width' => 400,
            'height' => 240,
            'collections' => ['default', 'wallpaper']
        ],
        'teaser' => [
            'type' => 'fit',
            'width' => 790,
            'height' => 440,
            'collections' => ['default','wallpaper']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 1600,
            'height' => 800,
            'collections' => ['default','wallpaper']
        ]
    ];

    protected $appends = ['teaser', 'main'];

    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function scopeFindSimilarSlugs(Builder $query, string $attribute, array $config, string $slug): Builder {
        return $query->withoutGlobalScope(ClientScope::class)->where($attribute, '=', $slug);
    }

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function category() {
        return $this->belongsTo(VenueCategory::class, 'venue_category_id');
    }

    public function city() {
        return $this->belongsTo(City::class,'city_id');
    }

    public function schemes() {
        return $this->hasMany(VenueScheme::class,'venue_id');
    }

    public function timetables() {
        return $this->hasMany(Timetable::class, 'venue_id');
    }

    /// *** Attributes *** ///

    public function getTeaserAttribute() {
        $src = $this->imagePrimarySrc('teaser');
        return $src ? env('APP_URL').$src : env('APP_URL').'/images/photo_placeholder.jpg';
    }

    public function getMainAttribute() {
        $src = $this->imagePrimarySrc('main');
        return $src ? env('APP_URL').$src : '';
    }

    /// *** Custom *** ///

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
        $obj->schemes()->create([
            'title' => [
                'kz' => 'Басты',
                'en' => 'Main',
                'ru' => 'Основной'
            ]
        ]);
        return $obj;
    }

    public function hasTimetables() {
        foreach($this->schemes as $scheme) {
            if($scheme->timetables()->count() > 0) return true;
        }
        return false;
    }

    public function delete() {
        if($this->hasTimetables()) {
            throw new BadRequestException('У данной площадки уже есть мероприятия');
        }
        foreach($this->schemes as $scheme) $scheme->delete();
        return parent::delete();
    }

}
