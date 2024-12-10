<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PDF\PDFTicket;
use App\Models\Specific\Order;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EventsController extends Controller {

    public function afisha($month = null) {
        $months = Timetable::getMonths();
        if(!$month && count($months) > 0) {
            return redirect('/'.app()->getLocale().'/afisha/'.$months[0]);
        }
        $q = Timetable::active();
        if($month) {
            $q->where('date', 'LIKE', $month.'%');
        }
        $items = $q->with('show')
            ->with('show.genre')
            ->with('show.hall')
            ->orderBy('date', 'asc')
            ->whereHas('show', function ($query) {
                $query->active();
            })->paginate(9);
        $submenu = $this->submenuFromMonths($months, 'afisha');
        return view('content.events.afisha', compact('items','months', 'month', 'submenu'));
    }

    public function repertoire($month = null) {
        $months = Timetable::getMonths();
        if(!$month && count($months) > 0) {
            return redirect('/'.app()->getLocale().'/repertoire/'.$months[0]);
        }
        $items = Show::active()->sorted()->paginate(9);
        $submenu = $this->submenuFromMonths($months, 'repertoire');
        return view('content.events.repertoire', compact('items','months', 'month', 'submenu'));
    }

    private function submenuFromMonths($months, $linkPart) {
        $menu = [];
        foreach($months as $m) {
            $menu[] = [
                'url' => $linkPart.'/'.$m,
                'title' => __(date('F', strtotime($m.'-01')))
            ];
        }
        return $menu;
    }

    public function show($slug) {
        $show = Show::findBySlug($slug);
        if(!$show || !$show->active) abort(404);
        $roles = $show->roles()->with('actors')->with('actors.actor')->get();
        $people = [];
        foreach($roles as $role) {
            $actors = $role->actors;
            foreach($actors as $a) {
                if($a->actor) {
                    $actor = $a->actor;
                    $actor->role = $role;
                    $people[] = $actor;
                }
            }
        }
        $gallery = $show->media()->where('collection_name','wallpaper')->get();
        $timetables = $show->timetables()->active()->where('date','>=', date('Y-m-d').' 00:00:00')->get();
        return view('content.events.show', compact('show', 'people', 'timetables', 'gallery'));
    }

    public function pdfTicket($order_id, $hash) {
        $query = Order::withoutGlobalScopes()
            ->where('id',$order_id)
            ->where('hash',$hash)
            ->where('paid',1);
        $order = $query->first();
        if(!$order) {
            abort(404);
        }
        Config::set('client_id', $order->client_id);
        $ticket = new PDFTicket($order);
        return $ticket->stream();
    }


}
