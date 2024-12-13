<?php

namespace App\Models\General;

use App\Models\Types\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations;

    protected $table = 'settings';

    public $translatable = [
        'body'
    ];

    protected $fillable = [
        'body', 'description','code'
    ];

    protected $attributes = [
        'body' => '{"ru":"","kz":"","en":""}',
    ];

//    public static function customCreate($request) {
//        $data = $request->all();
//        $set = Setting::create($data);
//        return $set;
//    }
//
//    public function customUpdate($request) {
//        $data = $request->all();
//        $this->update($data);
//        return $this;
//    }

    public static function getSettings() {
        return Cache::remember('settings',3600, function() {
            return Setting::all()->keyBy('code');
        });
    }

    public static function getPaySystems() {
        return [
            ['id' => PaymentType::CARD, 'title' => PaymentType::CARD ],
            ['id' => PaymentType::CASH, 'title' => PaymentType::CASH ],
            ['id' => PaymentType::KASPI, 'title' => PaymentType::KASPI ],
            ['id' => PaymentType::FORUM, 'title' => PaymentType::FORUM ],
            ['id' => PaymentType::INVITATION, 'title' => PaymentType::INVITATION ],
            ['id' => PaymentType::PARTNER, 'title' => PaymentType::PARTNER ],
        ];
    }


}
