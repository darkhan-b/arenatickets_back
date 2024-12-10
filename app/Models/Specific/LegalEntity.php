<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;

class LegalEntity extends Model {

    protected $table = 'legal_entities';

    protected $fillable = [
        'title',
    ];

    /// *** Relations *** ///

    public function shows() {
        return $this->hasMany(Show::class);
    }

}
