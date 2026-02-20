<?php

use App\Http\Controllers\Auth\SendMagicLoginLinkController;
use App\Http\Controllers\MagicLoginController;
use App\Livewire\Auth\MagicLoginRequest;
use App\Livewire\ClientProjectRates\Form as ClientProjectRateForm;
use App\Livewire\ClientProjectRates\Index as ClientProjectRateIndex;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/magic-login')->name('home');
Route::redirect('/login', '/magic-login')->name('login');

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

Route::middleware('auth')->group(function () {
    Route::livewire('clients', ClientIndex::class)->name('clients.index');
    Route::livewire('clients/create', ClientForm::class)->name('clients.create');
    Route::livewire('clients/{client}/edit', ClientForm::class)->name('clients.edit');
    Route::livewire('projects', ProjectIndex::class)->name('projects.index');
    Route::livewire('projects/create', ProjectForm::class)->name('projects.create');
    Route::livewire('projects/{project}/edit', ProjectForm::class)->name('projects.edit');
    Route::livewire('client-project-rates', ClientProjectRateIndex::class)->name('client-project-rates.index');
    Route::livewire('client-project-rates/create', ClientProjectRateForm::class)->name('client-project-rates.create');
    Route::livewire('client-project-rates/{clientProjectRate}/edit', ClientProjectRateForm::class)->name('client-project-rates.edit');
});

require __DIR__.'/settings.php';
