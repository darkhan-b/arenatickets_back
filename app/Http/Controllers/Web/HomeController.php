<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\General\Page;
use App\Models\General\Subscription;
use App\Models\Helpers\SitemapHelper;
use App\Models\Specific\Resume;
use App\Models\Specific\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller {


//    public function test() {
//        SitemapHelper::generateSitemap();
//        dd("test");
//        Mail::send(new TestMail());
//    }

    public function langFile() {
        $lang = config('app.locale');
//        Cache::forget('lang.js');
        $strings = Cache::remember('lang.js',3600, function () use($lang) {
            $lang = config('app.locale');
            return file_get_contents(resource_path('lang/' . $lang . '.json'));
        });
        header('Content-Type: text/javascript');
        echo('window.lang = "' . $lang . '";');
        echo('window.i18n = ' . $strings . ';');
        exit();
    }

    public function langFileJson(Request $request) {
        $lang = $request->lang;
        return file_get_contents(resource_path('lang/' . $lang . '.json'));
    }

    public function widgetDocs() {
        return view('docs.widget_doc');
    }

    public function partnerDocs() {
        return view('docs.partner_doc');
    }

    public function kaspiDocs() {
        return view('docs.kaspi_doc');
    }

	public function tourniquetDocs() {
		return view('docs.tourniquet_doc');
	}

}
