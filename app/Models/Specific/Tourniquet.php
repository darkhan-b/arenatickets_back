<?php

namespace App\Models\Specific;

use App\Models\General\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tourniquet extends Model {

    protected $table = 'tourniquets';

	public $incrementing = false;

	protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'allowed_sectors',
        'error_message',
        'opened_for_all',
    ];

	protected $casts = [
		'allowed_sectors' 	=> 'json',
		'opened_for_all'	=> 'boolean',
	];

    /// *** Custom *** ///

	public static function getEnabledTimetableId() {
		$record = DB::table('tourniquet_timetable')->first();
		return $record?->timetable_id ?? null;
	}

	public static function recordEnabledTimetableId($value) {
		DB::table('tourniquet_timetable')->delete();
		if($value) DB::table('tourniquet_timetable')->insert(['timetable_id' => $value]);
	}

	public static function checkSectionError($tourniquetId, $sectionName) {
		$standardError = 'Ограничение по сектору, проход запрещен';
		$tourniquet = Tourniquet::find($tourniquetId);
		if(!$tourniquet) return $standardError;
		if($tourniquet->opened_for_all) return null;
		return in_array($sectionName, $tourniquet->allowed_sectors) ? null : ($tourniquet->error_message ?? $standardError);
	}

}
