<?php

use App\Http\Controllers\MagicLoginController;
use App\Http\Controllers\Auth\SendMagicLoginLinkController;
use App\Livewire\Auth\MagicLoginRequest;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/magic-login')->name('home');

Route::middleware('guest')->group(function () {
    Route::post('/magic-login', SendMagicLoginLinkController::class)
        ->middleware('throttle:6,1')
        ->name('magic-login.send');
    Route::get('/magic-login', MagicLoginRequest::class)->name('magic-login.request');
    Route::get('/magic-login/{token}', MagicLoginController::class)
        ->middleware('signed')
        ->name('magic-login.consume');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
