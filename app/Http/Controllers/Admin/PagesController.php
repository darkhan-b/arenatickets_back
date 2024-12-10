<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PagesController extends Controller
{
    private function getList($page) {
        return $page->children()->select('id','title_ru','sort_order')->orderBy('sort_order','desc')->get();
    }


    public function getPages(Request $request) {
        $id = $request->parent_id;
        $page = Page::find($id);
        return json_encode($this->getList($page));
    }

    public function savePage(Request $request) {
        $id = $request->parent_id;
        $page = Page::find($id);
        $page->children()->attach($request->child_id);
        return json_encode($this->getList($page));
    }

    public function deletePage(Request $request) {
        $id = $request->parent_id;
        $page = Page::find($id);
        $page->children()->detach($request->child_id);
    }


    public function incrementPage(Request $request) {
        $id = $request->parent_id;
        $page = Page::find($id);
        $q = $page->children()->where('child_id',$request->child_id);
        if($request->op == 'increment') {
            $q->increment('sort_order');
        }
        if($request->op == 'decrement') {
            $q->decrement('sort_order');
        }
        return json_encode($this->getList($page));
    }



}
