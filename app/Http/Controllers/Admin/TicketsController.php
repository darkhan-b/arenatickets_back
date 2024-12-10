<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Imports\TicketsImport;
use App\Models\Specific\ClosedSection;
use App\Models\Specific\Order;
use App\Models\Specific\Pricegroup;
use App\Models\Specific\SectionWithoutSeatSelection;
use App\Models\Specific\Ticket;
use App\Models\Specific\Timetable;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Section;
use App\Models\Specific\Show;
use App\Models\Specific\TicketDesign;
use App\Models\Specific\Tourniquet;
use App\Models\Specific\TourniquetPass;
use App\Models\Specific\Venue;
use App\Models\PDF\PDFTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TicketsController extends Controller {

	public function getTickets($timetable_id, $group_id) {
		$timetable = Timetable::findOrFail($timetable_id);
		return response()->json($timetable->getTicketsForGroup($group_id, true, true));
	}

	public function saveTickets($timetable_id, $group_id, Request $request) {
		$timetable = Timetable::findOrFail($timetable_id);
		$rules = [
			'price' => ['required', 'numeric', 'min:1'],
			'type'  => ['required', 'in:enter,seats'],
		];
		$type = $request->type;
		if($type == 'enter') {
			$rules['amount'] = ['required' ,'numeric', 'min:0'];
		}
		if($type == 'seats') {
			$rules['seats'] = ['required', 'array', 'min:1'];
		}
		$request->validate($rules);
		$createInvitationOrder = (bool)$request->invitationOrder;
		$hidePrice = (bool)$request->hidePrice;
		$tickets = Ticket::generateTickets($timetable, $group_id, $request->price, $type == 'enter' ? $request->amount : $request->seats, $type, $createInvitationOrder);
		if($createInvitationOrder) {
			return response()->json(Order::generateInvitationFromTickets($tickets, $hidePrice, $request->comment));
		}
		return $this->getTickets($timetable_id, $group_id);
	}


	public function deleteTickets($timetable_id, $group_id, Request $request) {

		$timetable = Timetable::findOrFail($timetable_id);

		$timetable->tickets()
			->where('section_id',$group_id)
			->whereIn('seat_id',$request->seats)
			->available()
			->delete();

		return $this->getTickets($timetable_id, $group_id);
	}


	public function sendTickets($id) {
		$order = Order::withoutGlobalScopes()
			->where('id', $id)
			->where('paid', 1)
			->first();
		if(!$order) {
			abort(404);
		}
		return response()->json($order->sendByEmail());
	}


	public function orderDetails($id) {
		$order = Order::withTrashed()->findOrFail($id);
		$order->orderItems;
		$order->timetable;
		$html = view('admin.orderdetails',compact('order'))->render();
		return response()->json([
			'order' => $order,
			'html'  => $html
		]);
	}

	public function toggleClosedSection($timetable_id, $section_id, $action) {
		if($action == 1) {
			ClosedSection::create(compact('timetable_id', 'section_id'));
		} else {
			ClosedSection::where(compact('timetable_id', 'section_id'))->delete();
		}
		return response()->json(ClosedSection::where('timetable_id', $timetable_id)->get());
	}

	public function toggleSeatSelectionSection($timetable_id, $section_id, $action) {
		if($action == 1) {
			SectionWithoutSeatSelection::create(compact('timetable_id', 'section_id'));
		} else {
			SectionWithoutSeatSelection::where(compact('timetable_id', 'section_id'))->delete();
		}
		return response()->json(SectionWithoutSeatSelection::where('timetable_id', $timetable_id)->get());
	}

	public function uploadTicketsFromExcel($timetable_id, Request $request) {
		$pricegroup = null;
		if($request->pricegroupTitle && $request->pricegroupPrice) {
			$pricegroup = Pricegroup::create([
				'timetable_id'  => $timetable_id,
				'title'         => $request->pricegroupTitle,
				'price'         => $request->pricegroupPrice,
			]);
		}
		Excel::import(new TicketsImport($timetable_id, $request->sectionId, $pricegroup), $request->file('file'));
		return response()->json(true);
	}

	public function pdfticketExample($id, Request $request) {
		if($request->tkn != env('X_API_TOKEN')) {
			return $request->tkn;
		}
		$design = TicketDesign::findOrFail($id);
		foreach($design->getAttributes() as $k => $v) {
			if(isset($request->{$k})) {
				$val = $request->{$k};
				if($val === 'false') $val = 0;
				$design->{$k} = $val;
			}
		}
		$example_id = "999999999999";
		$event = new Show();
		$event->id = $example_id;
		$event->ticket_design_id = $id;
		$event->title = "Концерт тестовой группы";
		$event->ticketDesign = $design;
		$timetable = new Timetable();
		$timetable->id = $example_id;
		$timetable->date = "2025-03-22 19:30:00";
		$timetable->show = $event;
		$order = new Order();
		$venue = new Venue();
		$venue->title = 'Дворец дружбы';
		$timetable->venue = $venue;
		$order->name = "Тестовый пользователь";
		$order->timetable_id = $example_id;
		$order->id = $example_id;
		$order->timetable = $timetable;
		$orderitem = new OrderItem();
		$orderitem->order = $order;
		$orderitem->id = $example_id;
		$orderitem->order_id = $example_id;
		$orderitem->section_id = 20;
		$orderitem->row = 4;
		$orderitem->seat = 12;
		$orderitem->price = 1000;
		$orderitem->barcode = $example_id;
		$section = new Section();
		$section->title = 'Сектор D';
		$orderitem->section = $section;
		$order->orderItems = [$orderitem];
		$pdf = new PDFTicket($order);

		return $pdf->stream();
	}

	public function getTourniquetData(Request $request) {
		return response()->json([
			'tourniquets' 	=> Tourniquet::orderBy('note', 'asc')->get(),
			'timetable_id' 	=> Tourniquet::getEnabledTimetableId()
		]);
	}

	public function syncTourniquetSettings(Request $request) {
		$request->validate([
			'timetable_id' => ['numeric', 'nullable'],
			'tourniquets'  => ['array']
		]);
		Tourniquet::recordEnabledTimetableId($request->timetable_id);
		$data = $request->tourniquets;
		DB::transaction(function() use($data) {
			foreach($data as $item) {
				Tourniquet::where(['id' => $item['id']])->update($item);
			}
		});
		return $this->getTourniquetData($request);
	}

	public function getTourniquetLogs(Request $request) {
		return response()->json([
			'summary' 	=> TourniquetPass::getSummary()
		]);
	}

}
