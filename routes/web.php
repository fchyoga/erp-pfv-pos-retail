<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pos');
});

Route::get('/pos', \App\Livewire\PosIndex::class)->middleware('auth')->name('pos');

Route::any('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
})->name('logout');

Route::get('/admin/products/{product}/print-barcode', function (\App\Models\Product $product) {
    if (!auth()->check() || (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasPermissionTo('view_product'))) {
        abort(403, 'Unauthorized.');
    }
    return view('print-barcode', compact('product'));
})->name('products.print-barcode')->middleware(['web', 'auth']);

Route::get('/admin/reports/print', [\App\Http\Controllers\ReportPrintController::class, 'print'])
    ->name('reports.print')
    ->middleware(['web', 'auth']);
