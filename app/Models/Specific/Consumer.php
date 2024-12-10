<?php

namespace App\Models\Specific;

use App\Models\General\Role;
use App\Models\General\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Consumer extends User {

    protected static function booted() {
        static::addGlobalScope('validator', function (Builder $builder) {
            $builder->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->whereIn('model_has_roles.role_id',[2]);
        });
    }

    public function roles(): BelongsToMany {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

}
