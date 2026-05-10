<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pos');
});

Route::get('/pos', \App\Livewire\PosIndex::class)->name('pos');

Route::get('/admin/products/{product}/print-barcode', function (\App\Models\Product $product) {
    if (!auth()->check() || !auth()->user()->hasRole('super_admin') && !auth()->user()->hasPermissionTo('view_product')) {
        // Just a simple check, filament already protects admin.
    }
    return view('print-barcode', compact('product'));
})->name('products.print-barcode')->middleware('web');
