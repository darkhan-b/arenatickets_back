<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Meta extends Model
{
    use HasTranslations;

    protected $table = 'metas';

    public $translatable = [
        'meta_title', 'meta_description'
    ];

    protected $fillable = [
        'url',
        'meta_title',
        'meta_description'
    ];

    protected $attributes = [
        'meta_title'        => '{}',
        'meta_description'  => '{}',
    ];



}
