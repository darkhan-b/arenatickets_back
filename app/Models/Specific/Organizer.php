<?php

namespace App\Models\Specific;

use App\Models\General\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Organizer extends User {

    protected static function booted() {
        static::addGlobalScope('organizer', function (Builder $builder) {
            $builder->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id',4);
        });
    }

    public static function customCreate($request) {
        $obj = parent::customCreate($request);
        $obj->syncRolesManually([4]);
        return $obj;
    }



}
