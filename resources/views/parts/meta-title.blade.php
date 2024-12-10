@php
    $meta = isset($meta) ? $meta : null;
    $description = $meta && $meta->meta_description ? $meta->meta_description : __('default_description');
    $title = $meta && $meta->meta_title ? $meta->meta_title : __('default_title');
    $image = env('APP_URL').'/images/meta.jpeg';
@endphp

<meta charset="utf-8">
<title>@yield('meta-title',$title)</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="@yield('meta-description',$description)" />
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="langUrl" content="{{ $lang ?? 'ru' }}">

<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest" crossorigin="use-credentials">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

<meta property="og:url"                content="{!! Request::url() !!}" />
<meta property="og:type"               content="website" />
<meta property="og:title"              content="@yield('meta-title',$title)" />
<meta property="og:description"        content="@yield('meta-description',$description)"/>
<meta property="og:image"              content="@yield('meta-image',$image)" />

<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="@\{{ env('APP_NAME') }}" />
<meta name="twitter:title" content="@yield('meta-title',$title)"/>
<meta name="twitter:description" content="@yield('meta-description',$description)" />
<meta name="twitter:image" content="@yield('meta-image',$image)" />

<meta name="author" content="Offlime.kz">

<link rel="shortcut icon" type="image/png" href="/favicon-32x32.png"/>
<link rel="canonical" href="{{ Request::url() }}">

