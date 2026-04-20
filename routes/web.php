<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CheckProduct;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/check/{uuid}', CheckProduct::class)->name('product.check');
