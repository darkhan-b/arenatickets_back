<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;

trait Taggable {

//    public $tag_type = 'default';

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function getTagsNames() {
        return $this->tags()->pluck('title')->toArray();
    }

    public function getTagNamesString() {
        return implode(',',$this->getTagsNames());
    }

    public function syncTagsWithType($tags, $type) {
        $arr = [];
        foreach($tags as $t) {
            $tag = Tag::updateOrCreate(['title' => $t, 'type' => $type],[]);
            $arr[] = $tag->id;
        }
        $this->tags()->sync($arr);
        return $arr;
    }

    public function reTag($request) {
        $tags = $request->tags ? explode(',',$request->tags) : [];
        $tagids = $this->syncTagsWithType($tags,$this->tag_type);
        $this->recountTags($tagids);
    }

    public function recountTags($tags) {
        foreach($tags as $tid) {
            $tag = Tag::find($tid);
            $tag->count = DB::table('taggables')
                ->where('tag_id',$tid)
                ->where('taggable_type',$this->getMorphClass())->count();
            $tag->save();
        }
    }

}