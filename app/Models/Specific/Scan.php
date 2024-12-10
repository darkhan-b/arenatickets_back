<?php

namespace App\Models\Specific;

use App\Models\General\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Scan extends Model {

    protected $table = 'scans';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'timetable_id',
        'device_id',
        'barcode',
        'direction',
        'created_at'
    ];

    /// *** Relations *** ///

    public function timetable() {
        return $this->belongsTo(Timetable::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    /// *** Custom *** ///

    public static function timetableValidatedTickets($timetableId) {
        return Scan::where([
            'timetable_id' => $timetableId,
        ])->groupBy('barcode')
            ->select('barcode', DB::raw('count(*) as total'))
            ->get()
            ->count();
    }

}
