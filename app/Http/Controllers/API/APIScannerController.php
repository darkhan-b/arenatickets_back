<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Specific\Order;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Scan;
use App\Models\Specific\Timetable;
use App\Resources\TicketsForScannerResource;
use App\Resources\TimetablesForScannerResource;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class APIScannerController extends Controller {

    use APIResponseTrait;

    public function getTimetables(Request $request) {
        $user = $request->user();
        $q = Timetable::future()
            ->whereHas('show', function($query) use($user) {
                $query->where('organizer_id', $user->id)
                    ->orWhereHas('validators', function($q) use($user) {
                        $q->where('id', $user->id);
                    });
            })
            ->with('show')
            ->with('show.venue')
            ->orderBy('date','asc');
        $timetables = $q->get();
        return $this->apiSuccess(TimetablesForScannerResource::collection($timetables));
    }

    public function getTimetableCodes($id) {
        $orders = OrderItem::whereHas('order', function($query) use($id) {
            $query->where('paid', 1)->where('timetable_id', $id);
        })->select('order_id','row','seat','section_id','price','barcode')
            ->get();
        $orders = TicketsForScannerResource::collection($orders);
        return $this->apiSuccess($orders);

    }

    public function loadScans($id, Request $request) {
        $scans = $request->scans;
        $user = $request->user();
        $data = [];
        foreach($scans as $s) {
//            Log::error($s);
            $data[] = [
                'timetable_id'  => $id,
                'barcode'       => $s['barcode'],
                'created_at'    => date('Y-m-d H:i:s', strtotime($s['date'])),
                'device_id'     => $request->device_id,
                'user_id'       => $user->id,
            ];
        }
        $res = Scan::insert($data);
        $validated = Scan::timetableValidatedTickets($id);
        return $this->apiSuccess(compact('res', 'validated'));
    }

    public function getBarcodeHistory($timetableId, $barcode) {
        $scans = Scan::where([
            'timetable_id' => $timetableId,
            'barcode'      => $barcode
        ])->get();
        return $this->apiSuccess($scans);
    }

    public function getValidatedAmount($timetableId) {
//        $enters = Scan::where([
//            'timetable_id' => $timetableId,
//        ])->count();
        $barcodes = Scan::timetableValidatedTickets($timetableId);
//        $barcodes = Scan::where([
//            'timetable_id' => $timetableId,
//        ])->groupBy('barcode')->count();
        return $this->apiSuccess(compact('barcodes'));
    }


}
