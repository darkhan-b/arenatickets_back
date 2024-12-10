<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\PricegroupRequest;
use App\Models\Specific\Order;
use App\Models\Specific\Pricegroup;
use Illuminate\Http\Request;

class PricegroupsController extends Controller
{


    public function get($timetable_id) {
        $pricegroups = Pricegroup::where('timetable_id',$timetable_id)
            ->withCount('tickets')
			->with('sections')
            ->get();
        return response()->json($pricegroups);
    }


    public function save($timetable_id, PricegroupRequest $request) {
        if($request->id) {
            $pricegroup = Pricegroup::find($request->id);
            if($pricegroup) {
                $pricegroup->update([
                    'title'     => $request->title,
                    'price'     => $request->price,
					'color'     => $request->color,
                ]);
				$pricegroup->sections()->sync($request->sections ?? []);
            }
        } else {
            $pricegroup = Pricegroup::create([
                'timetable_id'  => $timetable_id,
                'title'         => $request->title,
                'price'         => $request->price,
            ]);
        }
        $createInvitationOrder = $request->invitationOrder ? true : false;
        $hidePrice = $request->hidePrice ? true : false;
        if($pricegroup) {
            $tickets = $pricegroup->setTicketsAmount($request->amount, $createInvitationOrder);
            if($createInvitationOrder) {
                return response()->json(Order::generateInvitationFromTickets($tickets, $hidePrice, $request->comment));
            }
        }
        return $this->get($timetable_id);
    }


    public function delete($timetable_id, $id) {
        $pricegroup = Pricegroup::find($id);
        $pricegroup->delete();
        return $this->get($timetable_id);
    }



}
