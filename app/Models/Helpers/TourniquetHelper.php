<?php

namespace App\Models\Helpers;

use App\Models\Specific\OrderItem;
use App\Models\Specific\Scan;
use App\Models\Specific\Tourniquet;
use App\Models\Specific\TourniquetLog;
use Illuminate\Http\Request;

class TourniquetHelper {

	private Request $request;
	private $orderItem;
	public $timetableId;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function validate() {
		$this->request->validate([
			'barcode' 		=> ['string', 'required'],
			'turnstile_id'	=> ['string', 'required']
		]);
	}

	public function log() {
		TourniquetLog::create([
			'ip' 	=> $this->request->ip(),
			'data'	=> $this->request->all(),
			'url'	=> $this->request->path()
		]);
	}

	public function getOrderItem() {
		$timetableId = Tourniquet::getEnabledTimetableId();
		if(!$timetableId) return null;
		$this->timetableId = $timetableId;
		$this->orderItem = OrderItem::where('barcode', $this->request->barcode)
			->whereHas('order', function($q) {
				$q->paid();
			})
			->where('timetable_id', $timetableId)
			->with(['section', 'pricegroup'])
			->first();
		return $this->orderItem;
	}

	public function getScanFromApp() {
		if(!$this->timetableId) {
			$this->timetableId = Tourniquet::getEnabledTimetableId();
		}
		if(!$this->timetableId) return null;
		return Scan::where([
			'timetable_id' => $this->timetableId,
			'barcode'      => $this->request->barcode
		])->first();
	}

	public function generateData() {
		return [
			'level' => $this->orderItem->section?->title ?? $this->orderItem->pricegroup?->title ?? null,
			'row'	=> $this->orderItem->row,
			'seat'	=> $this->orderItem->seat
		];
	}

	public function getSectorAllowedError() {
		return Tourniquet::checkSectionError($this->request->turnstile_id, $this->orderItem->section->getTranslation('title', 'ru'));
	}

	public function response($code, $message, $data) {
		return response()->json([
			'code' 		=> $code,
			'message' 	=> $message,
			'data'		=> $data
		]);
	}

	public function noDataResponse($code, $message) {
		return response()->json([
			'code' 		=> $code,
			'message' 	=> $message,
		]);
	}

}
