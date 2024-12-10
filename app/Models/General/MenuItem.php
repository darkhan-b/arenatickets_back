<?php

namespace App\Models\General;

use App\Traits\ActiveScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;
    use ActiveScopeTrait;
    
    protected $table = 'menu_items';

    protected $fillable = [
        'title',
        'url', 
        'sort_order',
        'parent_menu_item_id',
        'active',
        'header',
        'footer_1',
        'footer_2',
        'footer_3',
        'content_menu',
    ];

    protected $casts = [
        'header' => 'boolean',
        'footer_1' => 'boolean',
        'footer_2' => 'boolean',
        'footer_3' => 'boolean',
        'content_menu' => 'boolean',
    ];

    public $translatable = [
        'title'
    ];
    
    protected $attributes = [
        'title' => '{}',  
    ];

    public function parent() {
        return $this->belongsTo(MenuItem::class,'parent_menu_item_id');
    }

    public function children() {
        return $this->hasMany(MenuItem::class,'parent_menu_item_id');
    }

//    public static function customCreate($request) {
//        $data = $request->all();
//        $mi = MenuItem::create($data);
//        return $mi;
//    }
//
//    public function customUpdate($request) {
//        $data = $request->all();
//        $this->update($data);
//        return $this;
//    }

    public static function getMenu() {
        return Cache::remember('menu',3600, function() {
            return MenuItem::orderBy('sort_order','desc')->get();
        });
    }



}
