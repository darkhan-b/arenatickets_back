<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\General\News;
use App\Models\General\OneTimeAuth;
use App\Models\General\Page;
use App\Models\Specific\Article;
use App\Models\Specific\Order;
use App\Models\Specific\Partner;
use App\Models\Specific\PKPassGenerator;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use App\Models\General\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContentController extends Controller {

    public function home(Request $request) {
        $closest = Timetable::active()
            ->with('show')
            ->with('show.genre')
            ->with('show.hall')
            ->whereHas('show', function ($query) {
                $query->active();
            })->simplePaginate(4);
        $repertoire = Show::active()->simplePaginate(4);
        $news = News::active()->orderBy('date', 'desc')->paginate(3);
        $mainEvent = Show::getMainShow();
        return view('content.home.home', compact('news', 'closest', 'repertoire', 'mainEvent'));
    }
    
    public function about($slug = null) {
        if(!$slug) {
            return redirect('/'.app()->getLocale().'/about/history');
        }
        $page = Page::findBySlug($slug);
        if(!$page) abort(404);
        $blocks = $page->getCodedBlocks();
        return view('content.about.page', compact('page', 'blocks'));
    }
    
    public function contest($slug) {
        $item = Contest::findBySlug($slug);
        if(!$item || !$item->active) abort(404);
        return view('content.articles.contest', compact('item'));
    }

    public function article($slug) {
        $item = Article::findBySlug($slug);
        if(!$item || !$item->active) abort(404);
        return view('content.articles.article', compact('item'));
    }
    
    public function authWithToken(Request $request) {
        $token = $request->tkn;
        $user = User::where('api_token', $token)->first();
        if(!$user) {
            return json_encode(['tkn' => null]);    
        }
        $uuid = Str::uuid();
        OneTimeAuth::where(['user_id' => $user->id])->delete();
        OneTimeAuth::create([
            'token' => $uuid,
            'user_id' => $user->id
        ]);
        return json_encode(['tkn' => $uuid]);
    }
    
    public function widget(Request $request) {
        if($request->tkn) {
            $fullUrl = $request->fullUrl();
            $ota = OneTimeAuth::where('token', $request->tkn)->first();
            if($ota) {
                $url = str_replace('widget/', 'widget#/', $fullUrl);
                $url = str_replace($request->tkn, Crypt::encryptString($request->tkn), $url);
                $url = str_replace(env('APP_URL'), '', $url);
                return redirect($url);
//                Auth::loginUsingId($ota->user_id, true);
//                $ota->delete();
            }
            
        }
//        dd(Auth::user());
        return view('widget.widget', ['widget' => true]);
    }
    
    public function passkit($order_id, $hash, $order_item_id = null) {
        $order = Order::findByIdAndHash($order_id, $hash);
        if($order_item_id) {
            $oi = $order->orderItems()->find($order_item_id);
            return PKPassGenerator::generateTicket($oi, true);
        }
        $orderItems = $order->orderItems;
        return view('pdfs.passkit', compact('orderItems', 'order'));
    }
    


}
