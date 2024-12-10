<?php

namespace App\Models\Specific;

use App\Models\API\GoogleAPI;
use App\Models\General\Role;
use App\Scopes\ClientScope;
use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\ClientTrait;
use App\Traits\SortableTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Show extends Model {

    use HasTranslations, LogsActivity, ActiveScopeTrait, SortableTrait, AnimatedMedia, Sluggable, SluggableScopeHelpers, ClientTrait;

    protected $table = 'shows';

    public $translatable = [
        'title',
        'short_description',
        'description',
        'price',
        'note_danger',
        'note_important',
    ];

//    protected $hidden = ['description'];

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'category_id',
        'venue_id',
        'duration',
        'price',
        'discount',
        'refundable_fee',
        'age',
        'language',
        'ticket_design_id',
        'external_fee_value',
        'external_fee_type',
        'internal_fee_value',
        'internal_fee_type',
        'organizer_id',
        'legal_entity_id',
        'popular',
        'recommended',
        'video_url',
        'vendor',
        'vendor_id',
        'show_term_id',
        'internal_comment',
        'active',
        'sort_order',
        'organizer_add_status',
        'primary_media_id',
        'primary_media_ext',
        'note_danger',
        'note_important',
        'custom_button',
        'slug',
    ];

    protected $attributes = [
        'title'              => '{"ru":"","kz":"","en":""}',
        'short_description'  => '{"ru":"","kz":"","en":""}',
        'description'        => '{"ru":"","kz":"","en":""}',
        'price'              => '{"ru":"","kz":"","en":""}',
        'note_danger'        => '{"ru":"","kz":"","en":""}',
        'note_important'     => '{"ru":"","kz":"","en":""}',
        'external_fee_value' => 0,
        'external_fee_type'  => 'percent',
        'internal_fee_value' => 0,
        'internal_fee_type'  => 'percent',
        'refundable_fee'     => 0,
        'ticket_design_id'   => 1,
        'sort_order'         => 1
    ];

    protected $casts = [
        'title'             => 'json',
        'short_description' => 'json',
        'description'       => 'json',
        'price'             => 'json',
        'note_danger'       => 'json',
        'note_important'    => 'json',
        'active'            => 'boolean',
        'popular'           => 'boolean',
        'recommended'       => 'boolean',
        'custom_button'     => 'boolean',
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

    protected $appends = [
        'teaser',
        'main',
        'slide',
        'datePlaceString',
        'dateString',
        'placeString',
        'minCost',
        'city'
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Media *** ///

    public $media_dir = 'shows';

    public $store_primary_image = true;

    public $image_limit = 10;

    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 150,
            'height' => 100,
            'collections' => ['default','wallpaper','banner','mobile']
        ],
        'teaser' => [
            'type' => 'fit',
            'width' => 790,
            'height' => 440,
            'collections' => ['default','wallpaper']
        ],
        'slide' => [
            'type' => 'fit',
            'width' => 1860,
            'height' => 720,
            'collections' => ['default','wallpaper','banner']
        ],
        'mobile' => [
            'type' => 'fit',
            'width' => 800,
            'height' => 540,
            'collections' => ['mobile']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 1600,
            'height' => 800,
            'collections' => ['default','wallpaper']
        ]
    ];

    /// *** Model events *** ///

//    public static function boot() {
//        parent::boot();
//        static::updated(function($show) {
//            if($show->getOriginal('active') != $show->active) {
//                GoogleAPI::sendUrlToGoogle('event/'.$show->slug, $show->active ? 'add' : 'delete');
//            }
//        });
//    }

    /// *** Scopes *** ///

    public function scopeShowable($query) {
        return $query->active()->whereHas('timetables', function($q) {
            $q->future()->visibleTill();
        })->with(['timetables' => function($q) {
            $q->future()->visibleTill()->orderBy('date', 'asc');
        }]);
    }

    public function scopeFuture($query) {
        return $query->whereHas('timetables', function($q) {
            $q->future();
        });
    }

    public function scopePassed($query) {
        return $query->whereHas('timetables', function($q) {
            $q->passed();
        })->whereDoesntHave('timetables', function($q) {
            $q->future();
        });
    }

    public function scopeLocal($query) {
        return $query->whereNull('vendor');
    }

    public function scopeDoesNotRequireApproval($query) {
        return $query->where(function($q) {
            $q->whereNull('organizer_add_status')->orWhereNotIn('organizer_add_status', ['new', 'rejected']);
        });
    }

    public function scopeUserHasAccessToData($query, $user) {
        if($user->hasRole('manager_for_all')) return $query;
        if($user->hasRole('manager')) {
            $query->whereHas('managers', function($q) use($user) {
                $q->where('id', $user->id);
            });
        } else {
            $query->where('organizer_id', $user->id);
        }
    }

    /// *** Attributes *** ///

    public function getTeaserAttribute() {
        $teaser = $this->imagePrimarySrc('teaser');
        return env('APP_URL').($teaser ?: '/images/nophoto.jpeg');
    }

    public function getMainAttribute() {
        $main = $this->imagePrimarySrc('main');
        return env('APP_URL').($main ?: '/images/nophoto.jpeg');
    }

    public function getSlideAttribute() {
        $src = $this->imageSrc('slide', 'banner');
        if($src) {
            return env('APP_URL').$this->imageSrc('slide', 'banner');
        }
        return env('APP_URL').$this->imagePrimarySrc('slide');
    }

    public function getMobileSlideAttribute() {
        $src = $this->imageSrc('mobile', 'mobile');
        if($src) {
            return env('APP_URL').$this->imageSrc('mobile', 'mobile');
        }
        return $this->slide;
    }

    public function getPrimaryTimetableAttribute() {
        return $this->timetables()
            ->future()
            ->orderBy('date','asc')
            ->first();
    }

    public function getDatePlaceStringAttribute() {
        $timetable = $this->primaryTimetable;
        return $timetable ? $timetable->datePlaceString : '';
    }

    public function getDateStringAttribute() {
        $timetable = $this->primaryTimetable;
        return $timetable ? $timetable->dateString : '';
    }

    public function getPlaceStringAttribute() {
        $timetable = $this->primaryTimetable;
        return $timetable ? $timetable->placeString : '';
    }

    public function getMinCostAttribute() {
        $timetable = $this->primaryTimetable;
        return $timetable ? $timetable->minCost : '';
    }

    public function getCityAttribute() {
        return $this->venue ? $this->venue->city : null;
    }

    public function getDatesAttribute() {
        $timetables = $this->timetables()->future()->get();
        $minDate = null;
        $maxDate = null;
        foreach($timetables as $t) {
            if(!$minDate) $minDate = $t->date;
            if(!$maxDate) $maxDate = $t->date;
            if($t->date < $minDate) $minDate = $t->date;
            if($t->date > $maxDate) $maxDate = $t->date;
        }
        if(!$minDate && !$maxDate) return '';
        $minTime = strtotime($minDate);
        $maxTime = strtotime($maxDate);
        if($minDate == $maxDate) {
            return date('d', $minTime).' '.__(date('F', $minTime).'_');
        }
        if(date('m', $minTime) == date('m', $maxTime)) {
            return date('d', $minTime).' - '.date('d', $maxTime).' '.__(date('F', $minTime).'_');
        }
        return date('d', $minTime).' '.__(date('F', $minTime).'_').' - '.date('d', $maxTime).' '.__(date('F', $maxTime).'_');
    }

    /// *** Relations *** ///

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'show_category', 'show_id', 'category_id');
    }

    public function venue() {
        return $this->belongsTo(Venue::class);
    }

    public function roles() {
        return $this->hasMany(ShowRole::class, 'show_id');
    }

    public function timetables() {
        return $this->hasMany(Timetable::class, 'show_id')->orderBy('date', 'asc');
    }

    public function organizer() {
        return $this->belongsTo(Organizer::class);
    }

    public function validators() {
        return $this->belongsToMany(\App\Models\Specific\Validator::class, 'show_validators', 'show_id', 'validator_id');
    }

    public function managers() {
        return $this->belongsToMany(Manager::class, 'show_managers', 'show_id', 'manager_id');
    }

    public function cities() {
        return $this->belongsToMany(City::class, 'show_city', 'show_id', 'city_id');
    }

    public function primaryTimetable() {
        if(!$this->timetables) return null;

//        return $this->hasOne(Timetable::class, 'show_id')
//            ->future()
//            ->orderBy('date','asc')
//            ->take(1);
    }

    public function partners() {
        return $this->belongsToMany(APIPartner::class, 'partner_shows');
    }

    public function ticketDesign() {
        return $this->belongsTo(TicketDesign::class, 'ticket_design_id');
    }

    public function showTerm() {
        return $this->belongsTo(ShowTerm::class, 'show_term_id');
    }

    public function legalEntity() {
        return $this->belongsTo(LegalEntity::class);
    }

    /// *** Custom *** ///

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
        $obj->syncCategories($data);
        $obj->syncCities($data);
        $obj->validators()->sync($data['validators'] ?? []);
        $obj->managers()->sync($data['managers'] ?? []);
        return $obj;
    }


    public function customUpdate($request) {
        $data = $request->all();
        if(!isset($data['show_term_id'])) $data['show_term_id'] = null;
        if(!isset($data['language'])) $data['language'] = null;
        $this->update($data);
        $this->syncCategories($data);
        $this->syncCities($data);
        $this->validators()->sync($data['validators'] ?? []);
        $this->managers()->sync($data['managers'] ?? []);
        return $this;
    }

    public static function customShow($id) {
        $obj = self::find($id);
        $obj->makeVisible('description');
        return $obj;
    }

    public static function customValidationOnCreate($request, $obj = null) {
        Validator::make($request->all(), [
            'title.ru'   => ['required'],
//            'title.kz'   => ['required'],
//            'title.en'   => ['required'],
        ])->validate();
    }

    public static function customValidationOnUpdate($request, $obj = null) {
        Validator::make($request->all(), [
            'title.ru'   => ['required'],
//            'title.kz'   => ['required'],
//            'title.en'   => ['required'],
        ])->validate();
        if($obj && $obj->venue_id && $request->venue_id != $obj->venue_id) { // venue changed
            if(!$obj->venueCanBeChanged()) {
                throw ValidationException::withMessages(['show_message' => 'На текущей площадке уже заведены сеансы с рассадкой']);
            }
            $scheme = VenueScheme::where('venue_id', $request->venue_id)->first();
            $arr = [];
			if(isset($request->timetables) && $request->timetables) {
				foreach ($request->timetables as $rt) {
					$rt['venue_scheme_id'] = $scheme->id;
					$arr[] = $rt;
				}
			}
            $request->merge(['timetables' => $arr]);
        }
        if($request->external_fee_value > 100 && $request->external_fee_type == 'percent') {
            throw ValidationException::withMessages(['show_message' => 'Комиссия не может быть более 100%']);
        }
        if($request->internal_fee_value > 100 && $request->internal_fee_type == 'percent') {
            throw ValidationException::withMessages(['show_message' => 'Комиссия не может быть более 100%']);
        }

    }

    public function syncCategories($data) {
        //        $this->categories()->sync(isset($data['category_id']) && $data['category_id'] ? [$data['category_id']] : []);
        $this->categories()->sync($data['categories'] ?? []);
        $this->category_id = (isset($data['categories']) && $data['categories'] && count($data['categories'])) ? $data['categories'][0] : null;
        $this->save();
    }

    public function syncCities($data) {
        $cities = $data['cities'] ?? [];
        if($this->venue && $this->venue->city_id && !in_array($this->venue->city_id, $cities)) {
            $cities[] = $this->venue->city_id;
        }
        $this->cities()->sync($cities);
    }

    public function syncTimetables($data) {
        $ids = [];
        foreach($data as $d) {
            $active = (isset($d['active']) && $d['active']) ? 1 : 0;
            if($d['date']) {
                $fillData = [
                    'date'            => $d['date'],
                    'venue_scheme_id' => $d['venue_scheme_id'] ?? null,
                    'venue_id'        => $this->venue_id,
                    'active'          => $active
                ];
                if($d['id']) {
                    Timetable::where('id', $d['id'])->update($fillData);
                    $ids[] = $d['id'];
                } else {
                    $ttb = $this->timetables()->create($fillData);
                    $ids[] = $ttb->id;
                }
            }
        }
        $timetables = $this->timetables()->whereNotIn('id', $ids)->get();
        foreach($timetables as $ttb) {
            $ttb->delete();
        }
    }

    public static function collectByCategories($city = null) {
        $categories = Category::sorted()->get();
        $res = [];
        foreach($categories as $category) {
            $shows = $category->getShowsCarousel($city);
            if($shows && count($shows) > 0) {
                $res[] = [
                    'category' => $category,
                    'shows'    => $shows
                ];
            }
        }
        return $res;
    }

    public static function additionalSearchQuery($query, $search) {
        if(isset($search['category_id']) && $search['category_id']) {
            $query->whereHas('categories', function($q) use($search) {
                $q->where('id', $search['category_id']);
            });
        }
        if(isset($search['categories']) && $search['categories']) {
            $query->whereHas('categories', function($q) use($search) {
                $q->where('id', $search['categories']);
            });
        }
        if(isset($search['finished'])) {
            if($search['finished'] === 'true') $query->passed();
            if($search['finished'] === 'false') $query->future();
        }
        $query->doesNotRequireApproval();
        return $query;
    }

    public function venueCanBeChanged() {
        $timetables = $this->timetables;
        if(count($timetables) < 1) return true;
        foreach($timetables as $ttb) {
            if($ttb->type === 'sections') return false;
        }
        return true;
    }

    public function getPKPassStripImage() {
//        $stripPath = str_replace('.jpg', '.png', $this->imagePrimarySrc('thumbnail'));
        $stripPath = str_replace('.jpg', '.png', $this->imagePrimarySrc('strip'));
        $path = 'public'.str_replace('/storage', '', $stripPath);
        if(Storage::exists($path)) {
            return Storage::path($path);
        }
        $original = 'public'.str_replace('/storage', '', $this->imagePrimarySrc($this->id, true));
        Storage::copy($original, $path);
        $image = Image::make(Storage::path($path));
        $image->fit(375, 100)->save(null, null, 'png');
//        $image->resize(180, 180, function ($constraint) {
//                $constraint->aspectRatio();
//            })->save(null, null, 'png');
        return Storage::path($path);
    }

    public static function additionalListQuery($query) {
        $query->orderBy('id', 'desc');
        return $query;
    }

    public function delete() {
        if($this->timetables()->count() > 0) {
            throw ValidationException::withMessages(['show_message' => 'У события есть сеансы']);
        }
        return parent::delete();
    }

}
