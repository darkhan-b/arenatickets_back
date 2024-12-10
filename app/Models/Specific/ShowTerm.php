<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ShowTerm extends Model {

    use HasTranslations, LogsActivity;

    protected $table = 'show_terms';

    public $translatable = [
        'title'
    ];

    protected $fillable = [
        'title',
    ];

    protected $attributes = [
        'title' => '{}',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function shows() {
        return $this->hasMany(Show::class, 'show_term_id');
    }

    public function showTermPoints() {
        return $this->hasMany(ShowTermPoint::class, 'show_term_id');
    }

    /// *** Custom *** ///

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
        $obj->syncPoints($data['showTermPoints'] ?? []);
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        $this->update($data);
        $this->syncPoints($data['showTermPoints'] ?? []);
        return $this;
    }

	public function customReplicate() {
		$clone = $this->replicate();
		$clone->push();
		foreach ($this->showTermPoints as $showTermPoint) {
			$clonedSTP = $showTermPoint->replicate();
			$clone->showTermPoints()->save($clonedSTP);
		}
		return $clone;
	}

    public function syncPoints($points) {
        $this->showTermPoints()->delete();
        $this->showTermPoints()->createMany($points);
    }

}
