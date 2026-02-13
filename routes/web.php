<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\{Dashboard, Clients, Projects, Invoices, LedgerProject};

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/admin/login', Login::class)
    ->middleware('guest')
    ->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('/admin')->group(function() {
         Route::get('/clients', Clients::class)->name('admin.clients.index');
         Route::get('/projects', Projects::class)->name('admin.projects.index');
         Route::get('/invoices', Invoices::class)->name('admin.invoices.index');
         Route::get('/ledgers', LedgerProject::class)->name('admin.ledger.index');
    });

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

