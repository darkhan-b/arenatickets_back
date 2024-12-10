<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\General\News;
use Illuminate\Http\Request;

class NewsController extends Controller {

    public function news(Request  $request) {
        $q = News::active()->orderBy('date','desc');
//        if($request->category) {
//            $q->where('news_group', $request->category);
//        }
        $news = $q->paginate(9);
//        $groups = News::groups();
//        $category = null;
        return view('content.news.news', compact('news'));
    }

//    public function blogCategory($category) {
//        $news = News::active()->orderBy('date','desc')
//            ->where('news_group', $category)->paginate(8);
//        $groups = News::groups();
//        return view('content.blog.blog', compact('news','groups','category'));
//    }

    public function newsItem($slug) {
        $item = News::findBySlug($slug);
        if(!$item) abort(404);
        $other = News::active()->orderBy('date','desc')->where('id', '<>', $item->id)->take(3)->get();
        return view('content.news.newsitem', compact('item', 'other'));
    }

   
}
