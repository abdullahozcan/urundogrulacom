<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Livewire\CheckProduct;

Route::get('/locale/{lang}', function ($lang) {
    if (array_key_exists($lang, config('languages.supported'))) {
        session()->put('locale', $lang);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', function () {
    $posts = \App\Models\Post::where('is_published', true)->where('show_on_homepage', true)->latest()->take(3)->get();
    $partners = \App\Models\Partner::where('is_active', true)->orderBy('sort_order', 'asc')->get();
    return view('welcome', compact('posts', 'partners'));
});

Route::get('/check/{uuid}', CheckProduct::class)->name('product.check');

Route::get('/blog/{slug}', function ($slug) {
    $locales = array_keys(config('languages.supported', ['en' => 'English']));
    
    $post = \App\Models\Post::where('is_published', true)
        ->where(function($query) use ($slug, $locales) {
            foreach ($locales as $lc) {
                $query->orWhere("slug->{$lc}", $slug);
            }
        })
        ->firstOrFail();

    return view('blog.show', compact('post'));
})->name('blog.show');

Route::get('/iletisim', [ContactController::class, 'show'])->name('contact');
Route::post('/iletisim', [ContactController::class, 'send'])->middleware('throttle:5,10')->name('contact.send');

Route::get('/p/{slug}', function ($slug) {
    $locales = array_keys(config('languages.supported', ['en' => 'English']));
    
    $page = \App\Models\Page::where('is_published', true)
        ->where(function($query) use ($slug, $locales) {
            foreach ($locales as $lc) {
                $query->orWhere("slug->{$lc}", $slug);
            }
        })
        ->firstOrFail();

    return view('pages.show', compact('page'));
})->name('page.show');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/items/{item}/qr-png', [\App\Http\Controllers\QrDownloadController::class, 'downloadItemPng'])->name('items.qr-png');
    Route::get('/items/{item}/qr-pdf', [\App\Http\Controllers\QrDownloadController::class, 'downloadItemPdf'])->name('items.qr-pdf');
    Route::get('/products/{product}/qr-png', [\App\Http\Controllers\QrDownloadController::class, 'downloadProductPng'])->name('products.qr-png');
    Route::get('/products/{product}/qr-pdf', [\App\Http\Controllers\QrDownloadController::class, 'downloadProductPdf'])->name('products.qr-pdf');
});

Route::get('/sitemap.xml', function () {
    $posts = \App\Models\Post::where('is_published', true)->latest()->get();
    $pages = \App\Models\Page::where('is_published', true)->latest()->get();
    $defaultLocale = config('languages.default', 'tr');

    return response()->view('sitemap', compact('posts', 'pages', 'defaultLocale'))
                     ->header('Content-Type', 'text/xml');
});
