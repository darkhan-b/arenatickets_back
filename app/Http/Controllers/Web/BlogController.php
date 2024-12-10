<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\General\News;
use Illuminate\Http\Request;

class BlogController extends Controller {

    public function blog(Request  $request) {
        $q = News::active()->orderBy('date','desc');
        if($request->category) {
            $q->where('news_group', $request->category);
        }
        $news = $q->paginate(8);
        $groups = News::groups();
        $category = null;
        return view('content.blog.blog', compact('news','groups','category'));
    }

    public function blogCategory($category) {
        $news = News::active()->orderBy('date','desc')
            ->where('news_group', $category)->paginate(8);
        $groups = News::groups();
        return view('content.blog.blog', compact('news','groups','category'));
    }

    public function blogItem($slug) {
        if(in_array($slug, News::groups())) {
            return $this->blogCategory($slug);
        }
        $item = News::findBySlug($slug);
        if(!$item) {
            abort(404);
        }
        $last = News::active()->orderBy('date','desc')->take(3)->get();
        $groups = News::groups();
        $cnts = News::active()
            ->groupBy('news_group')
            ->selectRaw('COUNT(*) as cnt, news_group')
            ->get()
            ->keyBy('news_group');
        $total = 0;
        foreach($cnts as $n) {
            $total += $n->cnt;
        }
        return view('content.blog.blogitem', compact('item', 'last', 'groups', 'cnts', 'total'));
    }
}
