<?php

namespace App\Models\Specific;

use App\Traits\AnimatedMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TicketDesign extends Model {

    use LogsActivity;
    use AnimatedMedia;

    protected $table = 'ticket_designs';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'title_exists',
        'title_l',
        'title_r',
        'title_t',
        'ticketn_exists',
        'ticketn_l',
        'ticketn_r',
        'ticketn_t',
        'barcode_exists',
        'barcode_l',
        'barcode_r',
        'barcode_t',
        'qr_exists',
        'qr_l',
        'qr_r',
        'qr_t',
        'date_exists',
        'date_l',
        'date_r',
        'date_t',
        'section_exists',
        'section_l',
        'section_r',
        'section_t',
        'row_exists',
        'row_l',
        'row_r',
        'row_t',
        'seat_exists',
        'seat_l',
        'seat_r',
        'seat_t',
        'order_exists',
        'order_l',
        'order_r',
        'order_t',
        'user_exists',
        'user_l',
        'user_r',
        'user_t',
        'price_exists',
        'price_l',
        'price_r',
        'price_t',
        'conditions_exist',
        'bottom_data_duplicate_exists'
    ];

    protected $attributes = [
        'title'              => '',
        'title_exists'       => 1,
        'title_l'            => 119,
        'title_r'            => 0,
        'title_t'            => 200,
        'ticketn_exists'     => 1,
        'ticketn_l'          => 0,
        'ticketn_r'          => 118,
        'ticketn_t'          => 122,
        'barcode_exists'     => 1,
        'barcode_l'          => 824,
        'barcode_r'          => 0,
        'barcode_t'          => 1380,
        'qr_exists'          => 1,
        'qr_l'               => 0,
        'qr_r'               => 118,
        'qr_t'               => 218,
        'date_exists'        => 1,
        'date_l'             => 530,
        'date_r'             => 0,
        'date_t'             => 300,
        'section_exists'     => 1,
        'section_l'          => 530,
        'section_r'          => 0,
        'section_t'          => 330,
        'row_exists'         => 1,
        'row_l'              => 530,
        'row_r'              => 0,
        'row_t'              => 360,
        'seat_exists'        => 1,
        'seat_l'             => 530,
        'seat_r'             => 0,
        'seat_t'             => 390,
        'order_exists'       => 1,
        'order_l'            => 530,
        'order_r'            => 0,
        'order_t'            => 450,
        'user_exists'        => 1,
        'user_l'             => 530,
        'user_r'             => 0,
        'user_t'             => 480,
        'price_exists'       => 1,
        'price_l'            => 530,
        'price_r'            => 0,
        'price_t'            => 420,
        'conditions_exist'   => 0,
        'bottom_data_duplicate_exists' => 1
    ];

    protected $appends = ['teaser'];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Media *** ///

    public $media_dir = 'ticket_designs';

    public $image_limit = 1;

    public $conversions = [
        'teaser' => [
            'type' => 'fit',
            'width' => 600,
            'height' => 600,
            'collections' => ['default']
        ],
    ];

    /// *** Relations *** ///

    public function shows() {
        return $this->hasMany(Show::class, 'ticket_design_id');
    }

    /// *** Attributes *** ///

    public function getTeaserAttribute() {
        $teaser = $this->imagePrimarySrc('teaser');
        return env('APP_URL').($teaser ?: '/images/nophoto.jpeg');
    }

    /// *** Custom *** ///

    public function delete() {
        if($this->shows()->count() > 0) {
            throw ValidationException::withMessages(['show_message' => 'Этот дизайн уже используется в событиях']);
        }
        return parent::delete();
    }

}
