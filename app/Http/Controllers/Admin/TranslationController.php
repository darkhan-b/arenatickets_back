<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{

    public function getLangJSON($language = 'ru') {
        return json_encode([
            'file' => file_get_contents(resource_path('/lang/'.$language.'.json')),
            'langs' => explode(',',env('LANGS'))
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    public function saveTranslation(Request $request) {
        $lang = $request->lang;
        $string = $request->filecontent;
        $json = json_decode($string,true);
        if(json_last_error() != JSON_ERROR_NONE) {
            return back()->withErrors("Wrong content format");
        }
        ksort($json,SORT_STRING | SORT_FLAG_CASE);
        $string = json_encode($json,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(resource_path('lang/'.$lang.'.json'),$string);
        Cache::forget('lang.js');
        return back()->with('message',__("Saved"));
    }


    public static function addKeysFromEnToOtherLangs() {
        $ru_voc = json_decode(file_get_contents(resource_path('/lang/ru.json')),true);
        $langs = explode(',',env('LANGS'));
        foreach($langs as $l) {
            if($l == 'ru') {continue; }
            $voc = json_decode(file_get_contents(resource_path('/lang/'.$l.'.json')),true);
            $added_voc = array_merge($ru_voc,$voc);
            ksort($added_voc,SORT_STRING | SORT_FLAG_CASE);
            $string = json_encode($added_voc,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents(resource_path('lang/'.$l.'.json'),$string);
        }
        Cache::forget('lang.js');
    }

    public function manuallyAddKeysFromEnToOtherLangs() {
        self::addKeysFromEnToOtherLangs();
    }


}
