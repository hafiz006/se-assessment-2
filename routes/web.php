<?php

use App\Livewire\Client;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Client\Dashboard::class)->name('dashboard');

    Route::middleware('account.active')->group(function () {
        Route::get('deposits', Client\Deposits\Index::class)->name('deposits.index');
        Route::get('deposits/create', Client\Deposits\Create::class)->name('deposits.create');
        Route::get('deposits/{deposit}', Client\Deposits\Show::class)->name('deposits.show');

        Route::get('withdrawals', Client\Withdrawals\Index::class)->name('withdrawals.index');
        Route::get('withdrawals/create', Client\Withdrawals\Create::class)->name('withdrawals.create');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
