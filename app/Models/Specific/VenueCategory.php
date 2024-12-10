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

class VenueCategory extends Model {

    use HasTranslations, LogsActivity, ActiveScopeTrait, SortableTrait, Sluggable, SluggableScopeHelpers, ClientTrait;

    protected $table = 'venue_categories';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
    ];

    protected $attributes = [
        'title' => '{"ru":"","kz":"","en":""}',
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


}
