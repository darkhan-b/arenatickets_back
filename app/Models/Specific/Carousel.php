<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Crypto\Rsa\KeyPair;
use Spatie\Translatable\HasTranslations;

class Carousel extends Model {

    use HasTranslations;
    use LogsActivity;
    use AnimatedMedia;
    use ActiveScopeTrait;
    use SortableTrait;

    protected $table = 'carousels';

    public $translatable = [
        'title',
    ];

    protected $fillable = [
        'title',
        'sort_order',
        'active'
    ];

    protected $attributes = [
        'title'       => '{}',
        'sort_order'  => 1
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function timetables() {
        return $this->belongsToMany(Timetable::class, 'carousel_timetable', 'carousel_id', 'timetable_id');
    }

    /// *** Custom *** ///

    public static function collectForHome() {
        $res = [];
        $carousels = Carousel::active()->sorted()->with(['timetables:id,date'])->get();
        foreach($carousels as $carousel) {
            $res[] = $carousel->collectData();
        }
        return $res;
    }
    
    public function collectData() {
        $ids = $this->timetables()->pluck('id');
        return [
            'category' => $this,
            'shows'    => Show::showable()->whereHas('timetables', function($q) use($ids) {
                $q->whereIn('id', $ids);
            })->get()
        ];
    }

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
        $obj->save();
        $obj->timetables()->sync($request->timetables);
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        $this->update($data);
        $this->save();
        $this->timetables()->sync($request->timetables);
        return $this;
    }

}
