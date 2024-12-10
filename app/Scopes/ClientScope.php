<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ClientScope implements Scope
{
    public function apply(Builder $builder, Model $model) {
        if(!Config::get('superadmin', false)) {
            $builder->where('client_id', clientId());
        }
    }
}
