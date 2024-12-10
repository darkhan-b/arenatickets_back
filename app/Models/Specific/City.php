<?php

namespace App\Models\Specific;

use App\Scopes\ClientScope;
use App\Traits\ActiveScopeTrait;
use App\Traits\ClientTrait;
use App\Traits\SortableTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class City extends Model {

    use HasTranslations, LogsActivity, SortableTrait, ActiveScopeTrait, ClientTrait, Sluggable, SluggableScopeHelpers;

    protected $table = 'cities';

    public $timestamps = false;

    public $translatable = [
        'title',
        'description'
    ];

    protected $fillable = [
        'title',
        'description',
        'region_code',
        'sort_order',
        'active'
    ];

    protected $attributes = [
        'title'       => '{}',
        'description' => '{}',
        'sort_order'  => 1
    ];

    protected $casts = [
        'active'    => 'boolean',
    ];

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

    public function venues() {
        return $this->hasMany(Venue::class);
    }

    /// *** Custom *** ///

    public static function getCitiesList() {
        $collection = City::active()->sorted()->get();
        $collection->prepend([
            'id' => 'Kazakhstan',
            'title' => [
                'ru' => 'Все города',
                'en' => 'All cities',
                'kz' => 'Барлық қалалар'
            ]
        ]);
        return $collection;
    }

//    public static function guessCity(Request $request) {
//        if($request->city && $request->city != 'null') {
//            return null;
//        }
//        $location = Location::get($request->ip());
//        if($location && $location->regionCode) {
//            $city = City::where(['title->en' => $location->cityName])->first();
//            return $city ? $city->id : null;
//        }
//        return null;
//    }

}
