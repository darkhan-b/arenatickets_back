<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {

    protected $table = 'subscriptions';

    protected $fillable = [
        'email',
        'user_id',
        'ip'
    ];

    const UPDATED_AT = null;

    /// *** Relations *** ///

    public function user() {
        return $this->hasMany(User::class);
    }
}
