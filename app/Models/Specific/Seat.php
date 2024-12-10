<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model {

    protected $table = 'seats'; 

    public $timestamps = false;

    protected $fillable = [
        'row',
        'seat',
        'x',
        'y',
        'section_id'
    ];

    /// *** Relations *** ///

    public function section() {
        return $this->belongsTo(Section::class);
    }
}
