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
    $posts = \App\Models\Post::where('is_published', true)->latest()->take(3)->get();
    return view('welcome', compact('posts'));
});

Route::get('/check/{uuid}', CheckProduct::class)->name('product.check');
