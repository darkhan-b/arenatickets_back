<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;

class OneTimeAuth extends Model
{
    protected $table = 'one_time_auths';

    protected $fillable = [
        'token',
        'user_id', 
    ];

}
