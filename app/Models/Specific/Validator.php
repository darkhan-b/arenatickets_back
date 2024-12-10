<?php

namespace App\Models\Specific;

use App\Models\General\User;
use Illuminate\Database\Eloquent\Builder;

class Validator extends User {

    protected static function booted() {
        static::addGlobalScope('validator', function (Builder $builder) {
            $builder->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id',5);
        }); 
    }

}
