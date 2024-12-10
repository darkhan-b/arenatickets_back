<?php

function issetOrNull($var, $attr) {
    return isset($var[$attr]) ? $var[$attr] : null;
}

//use Carbon\Carbon;
//
//function targetBlank($url) {
//    return (strpos($url, 'http') === 0) ? '_blank' : '_self';
//}
//
//function limitLength($string) {
//    $limit = 40;
//    if(mb_strlen($string) > $limit) {
//        return mb_substr($string, 0, ($limit-3)).'...';
//    }
//    return $string;
//}
//
function presentNum($sum) {
    return number_format($sum,0,"."," ");
}

//function get_youtube_id_from_url($url) {
//    if (stristr($url,'youtu.be/')) {
//        preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID);
//        return $final_ID[4];
//    }
//    else {
//        @preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD);
//        return $IDD[5];
//    }
//}

function clientId() {
    return \Illuminate\Support\Facades\Config::get('client_id');
}

function setClientId($value) {
	return \Illuminate\Support\Facades\Config::set('client_id', $value);
}

function forceTranslations($input) {
    if(is_array($input)) {
        $langs = config('app.all_langs');
        foreach($langs as $l) {
            if(!($input[$l] ?? null)) $input[$l] = '';
        }
    }
    return $input;
}
//
//function presentSum($sum) {
//    return presentNum($sum).' &#8376;';
//}
//
//function currency() {
//    return ' &#8376;';
//}
//
function dateFormat($datetime, $time = false, $year = false) {
    $months = ['января','февраля',"марта","апреля","мая","июня","июля","августа","сентября","октября","ноября",
        "декабря"];
    $date_unix = strtotime($datetime);
    $month = $months[(date("m",$date_unix)-1)];
    $date = date("j",$date_unix) .' '. $month;
    if($year) {
        $date .= ' '.date('Y',$date_unix);
    }
    if ($time) {
        $date .= ', '. date("H:i",$date_unix);
    }
    return $date;
}

//function youtubeLink($link) {
//    $link = str_replace('watch?v=', 'embed/', $link);
//    return $link;
//}
//
//function fromStringToUTCString($string, $timezone) {
//    return Carbon::createFromFormat('Y-m-d H:i:s', $string, $timezone)
//        ->setTimezone('UTC')
//        ->format('Y-m-d H:i:s');
//}
//
//function zeroOneFromJavascript($value) {
//    $v = 0;
//    if($value && in_array($value, ['true',1])) {
//        $v = 1;
//    }
//    return $v;
//
//}
//
//function generateFileName($file,$extension = null) {
//    $part = time() .'_'.\Illuminate\Support\Str::random(6). '.';
//    if($extension) {
//        return $part.$extension;
//    }
//    return  $part.$file->getClientOriginalExtension();
//}
//
//
//function renderSlots($slot_id, $pages = null, $header = false, $classes = '', $svg = true) {
//    return \App\Models\Slot::renderSlots($slot_id, $pages, $header, $classes, $svg);
//}
//
//
//function convert_filesize($bytes, $decimals = 2) {
//    $size = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
//    $factor = floor((strlen($bytes) - 1) / 3);
//    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' '.@$size[$factor];
//}
//
//
function getNumbers($str) {
    return preg_replace('/[^0-9.]+/', '', $str);
}

//function localePath($url) {
//    if($url && $url[0] == '/') $url = substr($url, 1);
//    $lang = app()->getLocale();
//    $lang_url = '/'.(env('LANG_IN_LINK') ? $lang : '').'/'.$url;
//    return $lang_url;
//}

function settingProperty($settings, $property) {
    if(!isset($settings[$property])) return '';
    $body = $settings[$property]['body'];
    return $body;
}

function presentDuration($durationInMinutes) {
    if($durationInMinutes < 60) {
        return $durationInMinutes.' '.__('minutes');
    }
    $hours = floor($durationInMinutes / 60);
    $minutes = $durationInMinutes % 60;
    return $hours.' '.trans_choice('hours', $hours).($minutes > 0 ? ' '.$minutes.' '.__('minutes') : '');
}

function getXmlAttribute($object, $attribute) {
    if(isset($object[$attribute])) return (string) $object[$attribute];
    return null;
}

//function getLangUrl($lang = null) {
//    $lang_url = env('LANG_IN_LINK') ? '/'.($lang ? $lang : app()->getLocale()) : '';
//    return $lang_url;
//}
//
//
//function startsWith($haystack, $needle)
//{
//    $length = strlen($needle);
//    return (substr($haystack, 0, $length) === $needle);
//}
//
//function endsWith($haystack, $needle)
//{
//    $length = strlen($needle);
//    if ($length == 0) {
//        return true;
//    }
//
//    return (substr($haystack, -$length) === $needle);
//}
//
//function getExtensionFromUrl($url) {
//    $arr = explode(".", (is_array($url) ? $url[0] : $url));
//    $extension = end($arr);
//    return $extension ? $extension : '-';
//}
//
//
//if (!function_exists('mb_ucfirst')) {
//    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
//        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
//        $str_end = "";
//        if ($lower_str_end) {
//            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
//        }
//        else {
//            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
//        }
//        $str = $first_letter . $str_end;
//        return $str;
//    }
//}

function transformSVGPoints($points) {
    $str = 'M';
    foreach($points as $index => $p) {
        if($index > 0) $str .= ' l';
        $str .= $p[0].','.$p[1];
    }
    $str .= 'z';
    return $str;
}

function print_pre($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
