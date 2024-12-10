<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push(__('home'), route('home'));
});

// People
Breadcrumbs::for('people', function ($trail) {
    $trail->parent('home');
    $trail->push(__('theatre_people'), route('people'));
});

// Home > People > Person
Breadcrumbs::for('person', function ($trail, $item) {
    $trail->parent('people');
    $trail->push($item ? $item->name : '-', route('person', $item ? $item->slug : '-'));
});

// Home > News
Breadcrumbs::for('news', function ($trail) {
    $trail->parent('home');
    $trail->push(__('news'), route('news'));
});

// Home > News > Item
Breadcrumbs::for('newsitem', function ($trail, $item) {
    $trail->parent('news');
    $trail->push($item ? $item->title : '-', route('newsItem', $item ? $item->slug : '-'));
});

// Home > Contests
Breadcrumbs::for('contest', function ($trail, $item) {
    $trail->parent('page', new PageSkeleton(__('competition'), 'contests'));
    $trail->push($item ? $item->title : '-', route('contest', $item ? $item->slug : '-'));
});

// Home > Articles
Breadcrumbs::for('article', function ($trail, $item) {
    $trail->parent('page', new PageSkeleton(__('media_on_us'), 'media'));
    $trail->push($item ? $item->title : '-', route('article', $item ? $item->slug : '-'));
});

// Home > About
Breadcrumbs::for('about', function ($trail) {
    $trail->parent('home');
    $trail->push(__('about_theatre'), route('about'));
});

// Home > Contacts
Breadcrumbs::for('contacts', function ($trail) {
    $trail->parent('home');
    $trail->push(__('contacts'), route('contacts'));
});

// Home > Afisha
Breadcrumbs::for('afisha', function ($trail) {
    $trail->parent('home');
    $trail->push(__('afisha'), route('afisha'));
});

// Home > Repertoire
Breadcrumbs::for('repertoire', function ($trail) {
    $trail->parent('home');
    $trail->push(__('repertoire'), route('repertoire'));
});

// Home > Page
Breadcrumbs::for('page', function ($trail, $page) {
    $trail->parent('home');
    $trail->push($page ? $page->title : '-', route('page', $page ? $page->slug : '-'));
});


class PageSkeleton {
    
    public $title;
    public $slug;
    
    public function __construct($title, $slug) {
        $this->title = $title;
        $this->slug = $slug;
    }
}
