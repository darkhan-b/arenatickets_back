<?php

namespace App\Models\General;
use App\Traits\ActiveScopeTrait;
use App\Traits\AnimatedMedia;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasTranslations;
    use Sluggable;
    use SluggableScopeHelpers;
    use ActiveScopeTrait;
    use AnimatedMedia;

    protected $fillable = [
        'title',
        'body',
        'slug',
        'active',
    ];

    public $translatable = [
        'title', 'body'
    ];

    public function sluggable(): array {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    protected $attributes = [
        'title' => '{"ru":"","kz":"","en":""}',
        'body'  => '{"ru":"","kz":"","en":""}',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $appends = ['link', 'content'];

    /// *** Media *** ///

    public $media_dir = 'pages';

    public $conversions = [
        'thumb' => [
            'type' => 'fit',
            'width' => 150,
            'height' => 150,
            'collections' => ['default']
        ],
        'main' => [
            'type' => 'resize',
            'width' => 1600,
            'height' => 1200,
            'collections' => ['default']
        ]
    ];

    public function getLinkAttribute() {
        return '/'.$this->slug;
    }

    public function getContentAttribute() {
        $content = $this->body;
        $content = str_replace('src="/kcfinder', 'src="'.env('APP_URL').'/kcfinder', $content);
        return $content;
    }

    public function blocks() {
        return $this->hasMany(PageBlock::class, 'page_id');
    }

    /// *** Custom *** ///

    public static function customCreate($request) {
        $data = $request->all();
        $obj = self::create($data);
//        $obj->saveBlocks($data);
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        $this->update($data);
//        $this->saveBlocks($data);
        return $this;
    }

//    public function saveBlocks($data) {
//        if(!isset($data['blocks']) || !$data['blocks'] || count($data['blocks']) < 1) {
//            $this->blocks()->delete();
//            return;
//        }
//        $blocks = $data['blocks'];
//        foreach($blocks as $b) {
//            $b['page_id'] = $this->id;
//            if($b['id']) {
//                PageBlock::where('id', $b['id'])->update($b);
//            } else {
//                PageBlock::create($b);
//            }
//        }
//    }

    public function getCodedBlocks() {
        $blocks = $this->blocks()->sorted()->get()->groupBy('code');
        return $blocks;
        $arr = [];
        foreach($blocks as $k => $b) {
            $arr[$k] = $b[0]->content;
        }
        return $arr;
    }

}
