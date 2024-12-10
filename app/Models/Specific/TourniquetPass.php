<?php

namespace App\Models\Specific;

use App\Models\General\User;
use App\Models\Helpers\TourniquetHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TourniquetPass extends Model {

    protected $table = 'tourniquet_passes';

	public const UPDATED_AT = null;

	protected $fillable = [
		'turnstile_id',
		'barcode',
		'order_item_id',
		'timetable_id',
	];

	/// *** Relations *** ///

	public function orderItem() {
		return $this->belongsTo(OrderItem::class, 'order_item_id');
	}

	public function timetable() {
		return $this->belongsTo(Timetable::class, 'timetable_id');
	}

	/// *** Custom *** ///

	public static function getSummary() {
		$timetableId = Tourniquet::getEnabledTimetableId();
		$res = [
			'sold' 	 	=> 0,
			'passed' 	=> 0,
			'timetable' => null
		];
		if($timetableId) {
			$timetable = Timetable::find($timetableId);
			if($timetable) {
				$timetable->load('show');
				$timetable->append('datePlaceString');
				$res['sold'] = $timetable->soldTickets;
				$res['passed'] = TourniquetPass::where('timetable_id', $timetableId)->count();
				$res['timetable'] = $timetable;
			}
		}
		return $res;
	}

}
