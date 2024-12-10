<?php

namespace App\Traits;

use App\Models\General\User;
use App\Models\Specific\Client;
use App\Scopes\ClientScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait ClientTrait
{
    protected static function bootClientTrait(): void {

        if(static::class !== User::class) { // for login
            static::addGlobalScope(new ClientScope);
        }

        static::creating(function ($model) {
            if(clientId()) {
                $model->client_id = clientId();
            }
        });
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id');
    }

}
