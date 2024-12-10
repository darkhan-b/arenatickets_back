<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

class Tag extends Model
{
    use Sluggable;
    use SluggableScopeHelpers;

    protected $table  = 'tags';

    protected $fillable = ['title','type','count'];

    public function sluggable(): array {
        return [
            'slug' => ['source' => 'title']
        ];
    }

}
