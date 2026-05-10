<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pos');
});

Route::get('/pos', \App\Livewire\PosIndex::class)->name('pos');
