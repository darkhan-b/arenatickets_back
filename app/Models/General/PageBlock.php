<?php

namespace App\Models\General;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PageBlock extends Model
{
    use HasTranslations;
    use SortableTrait;

    protected $fillable = [
        'content',
        'code',
        'page_id',
        'sort_order',
        'block_type'
    ];

    public $translatable = [
        'content'
    ];

    protected $attributes = [
        'content' => '{}',
    ];

    /// *** Relations *** ///

    public function page() {
        return $this->belongsTo(Page::class, 'page_id');
    }

}
