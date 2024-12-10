<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model {

    protected $table = 'translations';

    protected $fillable = [
        'code', 'ru', 'en', 'kz'
    ];

    public $timestamps = false;

    public static function getTranslations() {
        return Translation::all()->pluck(app()->getLocale(), 'code')->toArray();
    }

    public static function transferFromFilesToDB() {
        $langs = ['ru', 'en', 'kz'];
        foreach($langs as $lang) {
            $translations = __('*', [], $lang);
            foreach($translations as $code => $translation) {
                Translation::updateOrCreate([
                    'code' => $code
                ], [
                    $lang => $translation
                ]);
            }
        }
    }

}
