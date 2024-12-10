<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Helpers\TourniquetHelper;
use App\Models\Specific\Scan;
use App\Models\Specific\TourniquetPass;
use App\Traits\APIResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class APITourniquetController extends Controller {

    use APIResponseTrait;

    public function verifyBarcode(Request $request) {
		$service = new TourniquetHelper($request);
		$service->log();
		$service->validate();
		$orderItem = $service->getOrderItem();
		if(!$orderItem) return $service->response(0, 'Билет не найден', null);
		$data = $service->generateData();
		$previousPass = TourniquetPass::where('order_item_id', $orderItem->id)->first();
		if($previousPass) return $service->response(2, 'Повторный проход', $data);
		$previousPassFromScanApp = $service->getScanFromApp();
		if($previousPassFromScanApp) return $service->response(2, 'Повторный проход', $data);
		$sectorAllowedError = $service->getSectorAllowedError();
		if($sectorAllowedError) return $service->response(3, $sectorAllowedError, $data);
		return $service->response(1, 'Проход разрешен', $data);
	}

	public function registerBarcode(Request $request) {
		$service = new TourniquetHelper($request);
		$service->log();
		$service->validate();
		$orderItem = $service->getOrderItem();
		if(!$orderItem) return $service->noDataResponse(0, 'Билет не найден');
		$previousPass = TourniquetPass::where('order_item_id', $orderItem->id)->first();
		if($previousPass) return $service->noDataResponse(2, 'Повторный проход');
		$previousPassFromScanApp = $service->getScanFromApp();
		if($previousPassFromScanApp) return $service->noDataResponse(2, 'Повторный проход');
		$sectorAllowedError = $service->getSectorAllowedError();
		if($sectorAllowedError) return $service->noDataResponse(3, $sectorAllowedError);
		try {
			TourniquetPass::create([
				'turnstile_id'	=> $request->turnstile_id,
				'barcode'		=> $request->barcode,
				'order_item_id'	=> $orderItem->id,
				'timetable_id'	=> $orderItem->timetable_id
			]);
			Scan::create([
				'timetable_id'	=> $orderItem->timetable_id,
				'barcode'		=> $request->barcode,
				'device_id'		=> $request->turnstile_id,
				'user_id'		=> null,
				'direction'		=> 'in',
				'created_at'	=> Carbon::now()
			]);
			return $service->noDataResponse(1, 'Проход зафиксирован');
		} catch (\Exception $e) {
			return $service->noDataResponse(0, 'Ошибка регистрации');
		}
	}

}
