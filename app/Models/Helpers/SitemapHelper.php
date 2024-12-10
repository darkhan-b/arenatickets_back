<?php

namespace App\Models\Helpers;

use App\Models\General\News;
use App\Models\Specific\Category;
use App\Models\Specific\City;
use App\Models\Specific\Contest;
use App\Models\Specific\Show;
use App\Models\Specific\Staff;
use App\Models\Specific\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class SitemapHelper {

    public static function generateSitemap($write_to_file = true) {

        $generator = SitemapGenerator::create(env('APP_FRONT_URL'))->getSitemap();
//        $langs = config('app.all_langs');
        $generator->add(Url::create(env('APP_FRONT_URL'))->setPriority(1)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
            ->setLastModificationDate(Carbon::now())
        );
        $langs = ['ru'];
        foreach($langs as $l) {

            $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l)->setPriority(1)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
                ->setLastModificationDate(Carbon::now())
            );
//            App::setLocale($l);
            $static_links = [
                'recommended',
                'afisha',
                'venues',
            ];
            $info_links = [
                'pages/about',
                'pages/purchase',
                'pages/refund',
                'pages/contacts'
            ];
                
            foreach($static_links as $s) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/'.$s)->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_ALWAYS)
                    ->setLastModificationDate(Carbon::now())
                );
            }
            foreach($info_links as $s) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/'.$s)->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            }
            $categories = Category::sorted()->get();
            foreach($categories as $category) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/category/'.$category->slug)->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            }
            $events = Show::showable()->get();
            foreach($events as $event) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/event/'.$event->slug)->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setLastModificationDate(Carbon::now())
                );
            }
            $venues = Venue::active()->get();
            foreach($venues as $venue) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/venue/'.$venue->slug)->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            }
            $cities = City::all();
            foreach($cities as $city) {
                $generator->add(Url::create(env('APP_FRONT_URL').'/'.$l.'/city/'.$city->slug)->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    ->setLastModificationDate(Carbon::yesterday())
                );
            }
        }

        if($write_to_file) {
            $generator->writeToFile(public_path('sitemap.xml'));
        }

    }
}
