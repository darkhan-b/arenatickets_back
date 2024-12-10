<?php

namespace App\Traits;

use App\Models\Meta;
use Illuminate\Http\Request;

trait MetaTaggable {

    public function getMetaTags($url) {
        $lang = app()->getLocale();
        $dict = $this->meta_tag_sources;
        $meta = Meta::whereIn('url',[$url,mb_substr($url,1)])->first();

        $title = $this->{'meta_title_'.$lang};
        if(!$title) {
            if($meta && $meta->{'meta_title_'.$lang}) {
                $title = $meta->{'meta_title_'.$lang};
            }
        }
        if(!$title) {
            $title = ($dict['title'] ? $this->{str_replace('{lang}',$lang,$dict['title'])} : null);
        }

        $keywords = $this->{'meta_keywords_'.$lang};
        if(!$keywords) {
            if($meta && $meta->{'meta_keywords_'.$lang}) {
                $keywords = $meta->{'meta_keywords_'.$lang};
            }
        }
        if(!$keywords) {
            $keywords = ($dict['keywords'] ? $this->{str_replace('{lang}',$lang,$dict['keywords'])} : null);
        }

        $description = $this->{'meta_description_'.$lang};
        if(!$description) {
            if($meta && $meta->{'meta_description_'.$lang}) {
                $description = $meta->{'meta_description_'.$lang};
            }
        }
        if(!$description) {
            $description = ($dict['description'] ? $this->{str_replace('{lang}',$lang,$dict['description'])} : null);
        }

        $image = isset($dict['image']) ? (($dict['image'] == 'imgSrc') ? str_replace('nophoto.png','logo.png',env('APP_URL').$this->imageSrc()) : null) : null;

        return [
            'title' => $title,
            'keywords' => $keywords,
            'description' => $description,
            'image' => $image,
        ];
    }



}