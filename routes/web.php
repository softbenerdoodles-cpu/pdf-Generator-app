<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Open routes
|--------------------------------------------------------------------------
|
| Everything needed to create and download a document works without an
| account. Signing in only adds somewhere to keep the result.
|
*/

Route::get('/', [GeneratorController::class, 'home'])->name('home');
Route::get('/templates', [GeneratorController::class, 'index'])->name('generator.index');
Route::post('/generate/parse', [GeneratorController::class, 'parse'])->name('generator.parse');
Route::post('/generate/preview', [GeneratorController::class, 'preview'])->name('generator.preview');
Route::get('/generate/{template}', [GeneratorController::class, 'create'])->name('generator.create');

Route::post('/pdf/preview', [PdfController::class, 'preview'])->name('pdf.preview');
Route::post('/pdf/download', [PdfController::class, 'download'])->name('pdf.download');

Route::get('/help', fn () => Inertia::render('Help'))->name('help');

/*
|--------------------------------------------------------------------------
| Accounts
|--------------------------------------------------------------------------
|
| An account is a convenience, not a gate: it adds saved documents, reusable
| business details, and a customer list.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/business', [AccountController::class, 'updateBusiness'])->name('account.business');
    Route::post('/account/customers', [AccountController::class, 'storeCustomer'])->name('account.customers.store');
    Route::delete('/account/customers/{customer}', [AccountController::class, 'destroyCustomer'])->name('account.customers.destroy');
});
