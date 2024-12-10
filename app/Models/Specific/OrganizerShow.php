<?php

namespace App\Models\Specific;

use App\Models\General\Spacemedia;
use App\Traits\AnimatedMedia;

class OrganizerShow extends Show {
    
    use AnimatedMedia;

    public static function boot()
    {
        parent::boot();

        self::updated(function($model) {
            Spacemedia::where([
                'model_type' => 'App\Models\Specific\OrganizerShow',
                'model_id'  => $model->id
            ])->update([
                'model_type' => 'App\Models\Specific\Show',
            ]);
        });
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
        $query->whereIn('organizer_add_status', ['new', 'rejected']);
        return $query;
    }    

}
