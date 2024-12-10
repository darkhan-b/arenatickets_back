<?php

namespace App\Models\Specific;

use App\Scopes\ClientScope;
use App\Traits\ActiveScopeTrait;
use App\Traits\SortableTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Category extends Model {

    use HasTranslations, LogsActivity, ActiveScopeTrait, SortableTrait, Sluggable, SluggableScopeHelpers;

    protected $table = 'categories';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
        'sort_order',
        'active'
    ];

    protected $attributes = [
        'title' => '{}',
    ];

    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

//    public function scopeFindSimilarSlugs(Builder $query, string $attribute, array $config, string $slug): Builder {
//        return $query->withoutGlobalScope(ClientScope::class)->where($attribute, '=', $slug);
//    }

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function shows() {
        return $this->belongsToMany(Show::class, 'show_category', 'category_id', 'show_id');
    }

    /// *** Custom *** ///

    public function getShowsCarousel($city = null) {
        $query = $this->shows()->showable();
        if($city) {
            $query->whereHas('cities', function($query) use($city) {
                $query->where('id', $city);
            });
//            $query->whereHas('venue', function($query) use($city) {
//                $query->where('city_id', $city);
//            });
        }
        return $query->paginate(6);
    }

}
