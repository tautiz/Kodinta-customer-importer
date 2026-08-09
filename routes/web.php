<?php

use App\Http\Controllers\CustomerImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CustomerImportController::class, 'index'])->name('imports.index');
Route::post('/imports/preview', [CustomerImportController::class, 'preview'])->name('imports.preview');
Route::post('/imports', [CustomerImportController::class, 'import'])->name('imports.store');
Route::delete('/imports/preview', [CustomerImportController::class, 'cancel'])->name('imports.cancel');
