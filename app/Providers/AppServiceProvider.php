<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::defaultView('vendor.pagination.dentist-pagination');
        
        $lang = 'ru';
        if (in_array(Request::segment(1), config('app.alt_langs'))) {
            $lang = Request::segment(1);
            config(['app.locale_prefix' => $lang]);
        }
        App::setLocale($lang);
        setlocale(LC_TIME, 'ru_RU.UTF-8'); 
        View::share(['lang' => $lang]);
    }
}
