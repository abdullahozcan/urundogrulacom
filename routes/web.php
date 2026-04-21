<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CheckProduct;

Route::get('/locale/{lang}', function ($lang) {
    if (array_key_exists($lang, config('languages.supported'))) {
        session()->put('locale', $lang);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', function () {
    $posts = \App\Models\Post::where('is_published', true)->where('show_on_homepage', true)->latest()->take(3)->get();
    return view('welcome', compact('posts'));
});

Route::get('/check/{uuid}', CheckProduct::class)->name('product.check');

Route::get('/blog/{slug}', function ($slug) {
    $locale = app()->getLocale();
    $defaultLocale = config('languages.default', 'tr');
    
    $post = \App\Models\Post::where('is_published', true)
        ->where(function($query) use ($slug, $locale, $defaultLocale) {
            $query->where("slug->{$locale}", $slug)
                  ->orWhere("slug->{$defaultLocale}", $slug);
        })
        ->firstOrFail();

    return view('blog.show', compact('post'));
})->name('blog.show');

Route::get('/iletisim', function () {
    return view('contact');
})->name('contact');

Route::get('/p/{slug}', function ($slug) {
    $locale = app()->getLocale();
    $defaultLocale = config('languages.default', 'tr');
    
    $page = \App\Models\Page::where('is_published', true)
        ->where(function($query) use ($slug, $locale, $defaultLocale) {
            $query->where("slug->{$locale}", $slug)
                  ->orWhere("slug->{$defaultLocale}", $slug);
        })
        ->firstOrFail();

    return view('pages.show', compact('page'));
})->name('page.show');

Route::get('/sitemap.xml', function () {
    $posts = \App\Models\Post::where('is_published', true)->latest()->get();
    $pages = \App\Models\Page::where('is_published', true)->latest()->get();
    $defaultLocale = config('languages.default', 'tr');

    return response()->view('sitemap', compact('posts', 'pages', 'defaultLocale'))
                     ->header('Content-Type', 'text/xml');
});
